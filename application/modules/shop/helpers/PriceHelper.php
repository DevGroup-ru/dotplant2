<?php

namespace app\modules\shop\helpers;


use app\modules\shop\models\Order;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use yii\caching\TagDependency;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;


class PriceHelper
{

    public static function getProductPrice(Product $product, Order $order = null, $quantity = 1, $type = null)
    {
        $price = $product->price;
        $cacheKey = 'PriceHelper::getProductPrice'
            . json_encode(
                [
                    $product->object->id,
                    $type
                ]
            );
        if (!$specialPriceList = Yii::$app->cache->get($cacheKey)) {
            $specialPriceListQuery = SpecialPriceList::find()
                ->where(['object_id' => $product->object->id])
                ->orderBy(['sort_order'=>SORT_ASC]);

            if ($type !== null) {
                $specialPriceListQuery->andWhere(
                    [
                        'type' => $type
                    ]
                );
            }
            $specialPriceList = $specialPriceListQuery->all();

            Yii::$app->cache->set(
                $cacheKey,
                $specialPriceList,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(
                                SpecialPriceList::className()
                            )
                        ]
                    ]
                )
            );
        }
        foreach ($specialPriceList as $specialPriceRow) {
            $class = $specialPriceRow->class;
            $handler = $specialPriceRow->handler;
                $price = $class::$handler($product, $order, $specialPriceRow, $price);
        }
        $resultingPrice = $price*$quantity;

        return $resultingPrice;
    }

    public static function getOrderPrice(Order $order, $type = null)
    {
        $price = 0;
        foreach ($order->items as $item) {
            $price += $item->total_price;
        }

        $cacheKey = 'PriceHelper::getOrderPrice'
            . json_encode([
                $order->object->id,
                $type
            ]);
        if (!$specialPriceList = Yii::$app->cache->get($cacheKey)) {
            $specialPriceListQuery = SpecialPriceList::find()
                ->where(['object_id' => $order->object->id])
                ->orderBy(['sort_order'=>SORT_ASC]);

            if ($type !== null) {
                $specialPriceListQuery->andWhere(['type' => $type]);
            }

            $specialPriceList = $specialPriceListQuery->all();

            Yii::$app->cache->set(
                $cacheKey,
                $specialPriceList,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(
                            SpecialPriceList::className()
                        )
                    ]
                ])
            );
        }
        foreach ($specialPriceList as $specialPriceRow) {
            $class = $specialPriceRow->class;
            $handler = $specialPriceRow->handler;
            $price = $class::$handler($order, $specialPriceRow, $price);
        }

        return $price;
    }
}