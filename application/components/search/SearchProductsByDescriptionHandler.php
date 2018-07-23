<?php

namespace app\components\search;


use app\modules\shop\models\Product;

class SearchProductsByDescriptionHandler implements SearchInterface
{
    public static function editQuery(SearchEvent $event)
    {
        /** @var \app\modules\shop\ShopModule $module */
        $module = \Yii::$app->modules['shop'];
        $product = \Yii::$container->get(Product::class);
        $event->activeQuery->select($product::tableName().'.`id`')
            ->from($product::tableName())
            ->orWhere('`name` LIKE :q')
            ->orWhere('`h1` LIKE :q')
            ->orWhere('`content` LIKE :q')
            ->orWhere('`sku` LIKE :q')
            ->addParams([':q' => '%' . $event->q . '%'])
            ->andWhere(
                [
                    'active' => 1,
                ]
            );
        if ($module->allowSearchGeneratedProducts != 1) {
            $event->activeQuery->andWhere(
                [
                    'parent_id' => 0
                ]
            );
        }
    }
}