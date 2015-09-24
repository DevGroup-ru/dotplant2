<?php

namespace app\modules\shop\commands;

use app\modules\shop\models\Yml;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use yii\helpers\Url;
use Yii;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\Json;
use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyStaticValues;

class YmlController extends \yii\console\Controller
{
    static $ymlEavProperties = [];
    static $ymlStaticProperties = [];
    static $noimg = '';

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

        if ($result == static::$noimg) {
            return '';
        }
        if ($tpl instanceof \Closure) {
            $result = call_user_func($tpl, $result);
        } else {
            $result = htmlspecialchars(trim(strip_tags($result)));
            $result = empty($result) ? $result : sprintf($tpl, $result);
        }

        return $result;
    }

    private function prepareProperties()
    {
        $props = Property::getDb()->cache(function ($db) {
            return Property::find()
                ->select('id, name, property_handler_id, key, property_group_id, has_static_values, is_eav, handler_additional_params')
                ->all();
        },
            86400,
            new TagDependency(['tags' => [
                ActiveRecordHelper::getCommonTag(Property::className()),
            ]]));
        foreach ($props as $one) {
            $additionalParams = Json::decode($one['handler_additional_params']);
            if (false === empty($additionalParams['use_in_file'])) {
                if (1 == $one['is_eav'] && false === isset(self::$ymlEavProperties[$one['id']])) {
                    self::$ymlEavProperties[$one['id']] = [
                        'name' => $one['name'],
                        'unit' => empty($additionalParams['unit']) ? '' : $additionalParams['unit'],
                        'key' => $one['key'],
                        'group_id' => $one['property_group_id'],
                        'handler_id' => $one['property_handler_id'],
                    ];
                } else if (1 == $one['has_static_values'] && false === isset(self::$ymlStaticProperties[$one['id']])) {
                    self::$ymlStaticProperties[$one['id']] = [
                        'name' => $one['name'],
                        'unit' => empty($additionalParams['unit']) ? '' : $additionalParams['unit'],
                    ];
                }

            }
        }
    }

    private function getValues($model)
    {
        /** @var  $model Product */
        $params = '';
        $eav = Yii::$app->getDb()->cache(function ($db) use ($model) {
            return (new Query())
                ->from($model->object->eav_table_name)
                ->select(Property::tableName() . '.id, ' . $model->object->eav_table_name . '.value')
                ->innerJoin(
                    Property::tableName(),
                    Property::tableName() . '.property_group_id = ' . $model->object->eav_table_name . '.property_group_id'
                    . ' AND ' . Property::tableName() . '.key = ' . $model->object->eav_table_name . '.key'
                )
                ->where([
                    'object_model_id' => $model->id,
                    $model->object->eav_table_name . '.key' => array_column(static::$ymlEavProperties, 'key'),
                    $model->object->eav_table_name . '.property_group_id' => array_column(static::$ymlEavProperties, 'group_id'),
                    Property::tableName() . '.id' => array_keys(static::$ymlEavProperties),
                ])
                ->andWhere(['<>','value', ''])
                ->all();
        });
        foreach ($eav as $prop) {
            if (false === isset($prop['id'])) continue;
            $unit = empty(static::$ymlEavProperties[$prop['id']]['unit'])
                ? ''
                : ' unit="' . static::$ymlEavProperties[$prop['id']]['unit'] . '"';
            $val = htmlspecialchars($prop['value']);
            switch (static::$ymlEavProperties[$prop['id']]['handler_id']) {
                case 3 :
                    $val = $val == 1 ? Yii::t('yii', 'Yes') : Yii::t('yii', 'No');
                    break;
            }
            $params .= '<param name="'
                . static::$ymlEavProperties[$prop['id']]['name'] . '"' . $unit . '>'
                . $val . '</param>'. PHP_EOL;
        }

        $psv = Yii::$app->getDb()->cache(function ($db) use ($model) {
            return  (new Query())
                ->from(PropertyStaticValues::tableName())
                ->innerJoin(
                    ObjectStaticValues::tableName(),
                    ObjectStaticValues::tableName() . '.property_static_value_id = ' . PropertyStaticValues::tableName() . '.id'
                )
                ->where([
                    'object_model_id' => $model->id,
                    'object_id' => $model->object->id,
                    'property_id' => array_keys(static::$ymlStaticProperties)
                ])
                ->andWhere(['<>','value', ''])
                ->all();
        });
        foreach ($psv as $prop) {
            if (false === isset($prop['property_id'])) continue;
            $unit = empty(static::$ymlStaticProperties[$prop['property_id']]['unit'])
                ? ''
                : ' unit="' . static::$ymlStaticProperties[$prop['property_id']]['unit'] . '"';
            $params .= '<param name="'
                . static::$ymlStaticProperties[$prop['property_id']]['name'] . '"' . $unit . '>'
                . htmlspecialchars($prop['value']) . '</param>'. PHP_EOL;
        }
        return $params;
    }

    public function actionGenerate()
    {
        $ymlConfig = new Yml();
        if (!$ymlConfig->loadConfig()) {
            return false;
        }
        static::$noimg  = Yii::$app->getModule('image')->noImageSrc;
        if (1 == $ymlConfig->offer_param) {
            $this->prepareProperties();
        }
        \Yii::$app->urlManager->setHostInfo($ymlConfig->shop_url);
        $filePath = \Yii::getAlias('@webroot') . '/' . $ymlConfig->general_yml_filename;
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
            $section_categories .= '<category id="' . $row['id'] . '" '
                . (0 != $row['parent_id'] ? 'parentId="' . $row['parent_id'] . '"' : '') . '>'
                . htmlspecialchars(trim(strip_tags($row['name']))) . '</category>' . PHP_EOL;
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

            $offer = '<offer id="' . $row->id . '" ' . $offer_type . ' available="true">' . PHP_EOL;

            /** @var Category $category */
            $category = $row->category;
            $category = empty($category) ? 1 : $category->category_group_id;
            $offer .= '<url>' . Url::to(['/shop/product/show', 'model' => $row, 'category_group_id' => $category], true) . '</url>' . PHP_EOL;
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_price', $row, '<price>%s</price>' . PHP_EOL);
            $offer .= '<currencyId>' . $ymlConfig->currency_id . '</currencyId>' . PHP_EOL;
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_category', $row, '<categoryId>%s</categoryId>' . PHP_EOL);
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_picture', $row,
                function ($value) use ($ymlConfig) {
                    if (empty($value)) {
                        return $value;
                    }
                    $value = '<picture>' . rtrim($ymlConfig->shop_url, '/') . $value . '</picture>' . PHP_EOL;
                    return $value;
                }
            );
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_name', $row,
                function ($value) use ($ymlConfig) {
                    if (mb_strlen($value) > 120) {
                        $value = mb_substr($value, 0, 120);
                        $value = mb_substr($value, 0, mb_strrpos($value, ' '));
                    }
                    $value = '<name>' . htmlspecialchars(trim(strip_tags($value))) . '</name>' . PHP_EOL;
                    return $value;
                }
            );
            $offer .= $this->wrapByYmlParam($ymlConfig, 'offer_description', $row,
                function ($value) use ($ymlConfig) {
                    if (mb_strlen($value) > 175) {
                        $value = mb_substr($value, 0, 175);
                        $value = mb_substr($value, 0, mb_strrpos($value, ' '));
                    }
                    $value = '<description>' . htmlspecialchars(trim(strip_tags($value))) . '</description>' . PHP_EOL;
                    return $value;
                }
            );
            if (1 == $ymlConfig->offer_param) {
                $offer .= $this->getValues($row);
            }
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
        $fileString = sprintf($ymlFileTpl,
            date('Y-m-d H:i'),
            $section_shop,
            $section_offers
        );
        if (1 == $ymlConfig->use_gzip) {
            file_put_contents(
                $filePath . '.gz',
                gzencode($fileString, 5)
            );
        }
        file_put_contents(
            $filePath,
            $fileString
        );
    }
}
