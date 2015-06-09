<?php

namespace app\modules\shop\commands;

use app\modules\shop\models\Yml;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use yii\helpers\Url;

class YmlController extends \yii\console\Controller
{
    private function getByYmlParam($yml, $name, $model, $default = '')
    {
        $param = $yml->$name;

        if ('field' === $param['type']) {
            $field = $param['key'];
            $result = $model->$field;
        } elseif ('relation' === $param['type']) {
            $rel = call_user_func([$model, $param['key']]);
            $attr = $param['value'];
            $rel = $rel->one();
            if (!empty($rel)) {
                $result = $rel->$attr;
            }
        }

        if (!empty($result)) {
            return $result;
        }

        return $default;
    }

    private function wrapByYmlParam($yml, $name, $model, $tpl)
    {
        $result = $this->getByYmlParam($yml, $name, $model);

        if ($tpl instanceof \Closure) {
            $result = call_user_func($tpl, $result);
        } else {
            $result = htmlspecialchars(trim(strip_tags($result)));
            $result = empty($result) ? $result : sprintf($tpl, $result);
        }

        return $result;
    }

    public function actionGenerate()
    {
        $ymlConfig = new Yml();
        if (!$ymlConfig->loadConfig()) {
            return false;
        }

        \Yii::$app->urlManager->setHostInfo($ymlConfig->shop_url);

        $tpl = <<< 'TPL'
        <name>%s</name>
        <company>%s</company>
        <url>%s</url>
        <currencies>
            <currency id="%s" rate="1" plus="0"/>
        </currencies>
        <categories>
            %s
        </categories>
        <store>%s</store>
        <pickup>%s</pickup>
        <delivery>%s</delivery>
        <local_delivery_cost>%s</local_delivery_cost>
        <adult>%s</adult>
TPL;

        $section_categories = '';
        $categories = Category::find()->where(['active' => 1])->asArray();
        /** @var Category $row */
        foreach ($categories->each(500) as $row) {
            $section_categories .= '<category id="'.$row['id'].'" '.(0 != $row['parent_id'] ? 'parentId="'.$row['parent_id'].'"' : '').'>'.htmlspecialchars(trim(strip_tags($row['name']))).'</category>' . PHP_EOL;
        }
        unset($row, $categories);

        $section_shop = sprintf($tpl,
            $ymlConfig->shop_name,
            $ymlConfig->shop_company,
            $ymlConfig->shop_url,
            $ymlConfig->currency_id,
            $section_categories,
            1 == $ymlConfig->shop_store ? 'true' : 'false',
            1 == $ymlConfig->shop_pickup ? 'true' : 'false',
            1 == $ymlConfig->shop_delivery ? 'true' : 'false',
            $ymlConfig->shop_local_delivery_cost,
            1 == $ymlConfig->shop_adult ? 'true' : 'false'
        );

        $section_offers = '';

//        $offer_type = ('simplified' === $ymlConfig->general_yml_type) ? '' : 'type="'.$ymlConfig->general_yml_type.'"';
        $offer_type = ''; // временно, пока не будет окончательно дописан механизм для разных типов

        $products = Product::find()->where(['active' => 1]);
        /** @var Product $row */
        foreach ($products->each(100) as $row) {
            $price = $this->getByYmlParam($ymlConfig, 'offer_price', $row, 0);
            $price = intval($price);
            if ($price <= 0 || $price >= 1000000000) {
                continue;
            }

            $offer = '<offer id="'.$row->id.'" '.$offer_type.' available="true">' . PHP_EOL;

            /** @var Category $category */
            $category = $row->category;
            $category = empty($category) ? 1 : $category->category_group_id;
            $offer .= '<url>'.Url::to(['/shop/product/show', 'model' => $row, 'category_group_id' => $category], true).'</url>' . PHP_EOL;
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_price', $row, '<price>%s</price>'. PHP_EOL);
            $offer .= '<currencyId>'.$ymlConfig->currency_id.'</currencyId>' . PHP_EOL;
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_category', $row, '<categoryId>%s</categoryId>' . PHP_EOL);
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_picture', $row,
                function($value) use ($ymlConfig)
                {
                    if (empty($value)) {
                        return $value;
                    }
                    $value = urlencode($value);
                    $value = '<picture>'.rtrim($ymlConfig->shop_url, '/').$value.'</picture>' . PHP_EOL;
                    return $value;
                }
            );
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_name', $row,
                function($value) use ($ymlConfig)
                {
                    if (mb_strlen($value) > 120) {
                        $value = mb_substr($value, 0, 120);
                        $value = mb_substr($value, 0, mb_strrpos($value, ' '));
                    }
                    $value = '<name>'.htmlspecialchars(trim(strip_tags($value))).'</name>'. PHP_EOL;
                    return $value;
                }
            );
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_description', $row,
                function($value) use ($ymlConfig)
                {
                    if (mb_strlen($value) > 175) {
                        $value = mb_substr($value, 0, 175);
                        $value = mb_substr($value, 0, mb_strrpos($value, ' '));
                    }
                    $value = '<description>'.htmlspecialchars(trim(strip_tags($value))).'</description>'. PHP_EOL;
                    return $value;
                }
            );

            $offer .= '</offer>';

            $section_offers .= $offer . PHP_EOL;
        }
        unset($row, $products);

        $ymlFileTpl = <<< 'TPL'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="%s">
    <shop>
        %s
        <offers>
            %s
        </offers>
    </shop>
</yml_catalog>
TPL;
        file_put_contents(
            \Yii::getAlias('@webroot').'/'.$ymlConfig->general_yml_filename,
            sprintf($ymlFileTpl,
                date('Y-m-d H:i'),
                $section_shop,
                $section_offers
            )
        );
    }
}
