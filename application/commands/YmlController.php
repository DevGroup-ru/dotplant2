<?php

namespace app\commands;

use app\backend\models\Yml;
use app\models\Category;
use app\models\Product;
use yii\helpers\Url;

class YmlController extends \yii\console\Controller
{
    private function getYmlTpl($header = true)
    {
        $tpl = <<< 'TPL'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="%s">
TPL;

        if ($header) {
            return sprintf($tpl, date('Y-m-d H:i'));
        }
        return '</yml_catalog>';
    }

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

        $result = htmlspecialchars(trim(strip_tags($result)));

        if (!empty($result)) {
            return sprintf($tpl, $result);
        }

        return '';
    }

    public function actionGenerate()
    {
        $ymlConfig = new Yml();
        if (!$ymlConfig->loadConfig()) {
            return false;
        }

        \Yii::$app->urlManager->setHostInfo($ymlConfig->shop_url);

        $section_shop = '<name>'.$ymlConfig->shop_name.'</name>' . PHP_EOL
            . '<company>'.$ymlConfig->shop_company.'</company>' . PHP_EOL
            . '<url>'.$ymlConfig->shop_url.'</url>' . PHP_EOL
            . '<currencies><currency id="'.$ymlConfig->currency_id.'" rate="1" plus="0" /></currencies>' . PHP_EOL;

        $section_shop .= '<categories>' . PHP_EOL;
        $categories = Category::find()->where(['active' => 1, 'is_deleted' => 0])->asArray();
        /** @var Category $row */
        foreach ($categories->each(500) as $row) {
            $section_shop .= '<category id="'.$row['id'].'" '.(0 != $row['parent_id'] ? 'parentId="'.$row['parent_id'].'"' : '').'>'.$row['name'].'</category>' . PHP_EOL;
        }
        unset($row, $categories);
        $section_shop .= '</categories>' . PHP_EOL
            . '<store>'.(1 == $ymlConfig->shop_store ? 'true' : 'false').'</store>' . PHP_EOL
            . '<pickup>'.(1 == $ymlConfig->shop_pickup ? 'true' : 'false').'</pickup>' . PHP_EOL
            . '<delivery>'.(1 == $ymlConfig->shop_delivery ? 'true' : 'false').'</delivery>' . PHP_EOL
            . '<local_delivery_cost>'.$ymlConfig->shop_local_delivery_cost.'</local_delivery_cost>' . PHP_EOL
            . '<adult>'.(1 == $ymlConfig->shop_adult ? 'true' : 'false').'</adult>' . PHP_EOL;

        $section_offers = '';


//        $offer_type = ('simplified' === $ymlConfig->general_yml_type) ? '' : 'type="'.$ymlConfig->general_yml_type.'"';
        $offer_type = ''; // временно, пока не будет окончательно дописан механизм для разных типов

        $products = Product::find()->where(['active' => 1, 'is_deleted' => 0]);
        /** @var Product $row */
        foreach ($products->each(100) as $row) {
            $offer = '<offer id="'.$row->id.'" '.$offer_type.' available="true">' . PHP_EOL;

            $offer .= '<url>'.Url::to(['product/show', 'model' => $row, 'category_group_id' => 1], true).'</url>' . PHP_EOL;
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_price', $row, '<price>%s</price>'. PHP_EOL);
            $offer .= '<currencyId>'.$ymlConfig->currency_id.'</currencyId>' . PHP_EOL;
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_category', $row, '<categoryId>%s</categoryId>' . PHP_EOL);
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_picture', $row, '<picture>'.rtrim($ymlConfig->shop_url, '/').'%s</picture>' . PHP_EOL);
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_name', $row, '<name>%s</name>'. PHP_EOL);
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_description', $row, '<description>%s</description>' . PHP_EOL);

            $offer .= '</offer>';

            $section_offers .= $offer . PHP_EOL;
        }
        unset($row, $products);


        file_put_contents(\Yii::getAlias('@webroot').'/'.$ymlConfig->general_yml_filename,
            $this->getYmlTpl() .
            '<shop>' . PHP_EOL .
            $section_shop . PHP_EOL .
            '<offers>'.$section_offers.'</offers>' . PHP_EOL .
            '</shop>' . PHP_EOL .
            $this->getYmlTpl(false)
        );
    }
}
?>