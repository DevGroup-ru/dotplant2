<?php

namespace app\components\search;


use app\models\BaseObject;
use app\models\ObjectStaticValues;
use app\models\PropertyStaticValues;
use app\modules\shop\models\Product;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class SearchProductsByPropertyHandler implements SearchInterface
{
    public static function editQuery(SearchEvent $event)
    {
        /** @var \app\modules\shop\ShopModule $module */
        $module = \Yii::$app->modules['shop'];

        /** @var \app\modules\shop\ShopModule $module */
        $properties = (new Query())
            ->select('`id`')
            ->from(PropertyStaticValues::tableName())
            ->where('`name` LIKE :q')
            ->addParams([':q' => '%' . $event->q . '%'])
            ->all();

        $product = \Yii::$container->get(Product::class);
        $event->activeQuery->select('{{%object_static_values}}.object_model_id')
            ->distinct(true)
            ->from(ObjectStaticValues::tableName())
            ->where('{{%object_static_values}}.object_id = :objectId')
            ->addParams([':objectId' => BaseObject::getForClass($product::className())->id])
            ->andWhere([
                'in',
                '{{%object_static_values}}.property_static_value_id',
                ArrayHelper::getColumn($properties, 'id')
            ]);

        if ($module->allowSearchGeneratedProducts != 1) {

            $event->activeQuery->innerJoin(
                '{{%product}}',
                '{{%product}}.id = {{%object_static_values}}.object_model_id'
            );

            $event->activeQuery->andWhere(
                [
                    '{{%product}}.parent_id' => 0,
                    '{{%product}}.active' => 1
                ]
            );
        }

        $event->setFunctionSearch(function ($activeQuery) {
            return ArrayHelper::getColumn($activeQuery->all(), 'object_model_id');
        });

    }
}