<?php

use app\models\PropertyStaticValues;
use app\models\Route;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Json;

class m160714_114125_warning_set_unique_static_value_key extends Migration
{
    protected static $log = [];

    public function up()
    {
        $svTable = PropertyStaticValues::tableName();
        $slugsArray = (new Query())
            ->select($svTable . '.slug')
            ->from($svTable)
            ->groupBy($svTable . '.slug')
            ->having('count(slug) > 1')
            ->all();

        $routs = Route::find()->asArray()->all();

        $filterProperties = [];
        foreach ($routs as $route) {
            if (empty($route['url_template']) === false) {
                foreach (Json::decode($route['url_template']) as $rules) {
                    if (empty($rules['property_id']) === false) {
                        $filterProperties[] = $rules['property_id'];
                    }
                }
            }
        }

        foreach ($slugsArray as $item) {
            $this->fixSlug($item['slug'], $filterProperties);

        }

        $fp = fopen(dirname(__FILE__) . '/.fix_property_slugs.csv', 'w');
        foreach (self::$log as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);


    }


    private function fixSlug($slug, $propertyFilterArray = [])
    {
        /**
         * @var $propertiesStaticValues PropertyStaticValues[]
         */
        $propertiesStaticValues = PropertyStaticValues::find()
            ->where(
                [
                    'slug' => $slug
                ]
            )
            ->all();

        $safePropertyValue = null;

        foreach ($propertiesStaticValues as $key => $propertyStaticValue) {
            if (in_array($propertyStaticValue->property_id, $propertyFilterArray) && $safePropertyValue === null) {
                $safePropertyValue = $propertyStaticValue;
                unset($propertiesStaticValues[$key]);
            }
        }

        if ($safePropertyValue === null && empty($propertiesStaticValues[0]) === false) {
            unset($propertiesStaticValues[0]);
        }

        foreach ($propertiesStaticValues as $propertyStaticValue) {
            $propertyStaticValue->slug = $this->newSlug($propertyStaticValue);
            if ($propertyStaticValue->save() === true) {
                self::$log[] = [
                    'id' => $propertyStaticValue->id,
                    'old_slug' => $slug,
                    'new_slug' => $propertyStaticValue->slug
                ];
            }
        }

    }


    private function newSlug(PropertyStaticValues $propertyStaticValue)
    {
        $validate = false;
        $num = 0;
        $slug = $propertyStaticValue->slug;
        while ($validate === false) {
            $num++;
            $propertyStaticValue->slug = $slug . '-' . $num;
            $validate = $propertyStaticValue->validate(['slug']);
        }

        return $propertyStaticValue->slug;

    }

    public function down()
    {
        echo "m160714_114125_warning_set_unique_static_value_key cannot be reverted.\n";

        return false;
    }
}
