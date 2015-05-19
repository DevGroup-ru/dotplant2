<?php

namespace app\modules\shop\components;


use app\modules\shop\models\Order;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use Yii;
use yii\caching\TagDependency;

class PriceHelper
{

    public static function getProductPrice(Product &$product, Order $order = null, $quantity = 1, $type = null)
    {
        if ($product->total_price == 0) {
            $product->total_price = $product->price;

            $cacheKey = 'PriceHelper::getProductPrice'.json_encode([$product->object->id, $type ]);
            if (!$specialPriceList = Yii::$app->cache->get($cacheKey)) {

                $specialPriceListQuery = SpecialPriceList::find()
                    ->where(['object_id'=>$product->object->id]);

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
                    new TagDependency([
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(SpecialPriceList::className())
                        ]
                    ])
                );
            }
            foreach($specialPriceList as $specialPriceRow) {
                $class = new $specialPriceRow->class;
                if ($class instanceof SpecialPriceProductInterface) {
                    $class->setPriceProduct($product, $order);
                }
            }
        }
        return $product->total_price * $quantity;
    }


}