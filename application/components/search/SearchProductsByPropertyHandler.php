<?php

namespace app\components\search;


use app\models\ObjectStaticValues;
use app\models\PropertyStaticValues;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class SearchProductsByPropertyHandler implements SearchInterface
{
    public static function editQuery(SearchEvent $event)
    {
        /** @var \app\modules\shop\ShopModule $module */
        $properties = (new Query())
            ->select('`id`')
            ->from(PropertyStaticValues::tableName())
            ->where('`name` LIKE :q')
            ->addParams([':q' => '%' . $event->q . '%'])
            ->all();


        $event->activeQuery->select('`object_model_id`')
            ->distinct(true)
            ->from(ObjectStaticValues::tableName())
            ->where('`object_id` = :objectId')
            ->addParams([':objectId' => 1])
            ->andWhere(['in', '`property_static_value_id`', ArrayHelper::getColumn($properties, 'id')]);

        $event->functionSearch = function ($activeQuery) {
            return ArrayHelper::getColumn($activeQuery->all(), 'object_model_id');
        };

    }
}