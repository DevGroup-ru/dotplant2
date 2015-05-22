<?php

namespace app\modules\shop\helpers;


use app\modules\shop\events\OrderCalculateEvent;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Discount;
use app\modules\shop\models\Order;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\models\SpecialPriceObject;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;

class PriceHandlers
{
    private static $_allDiscounts = [];


    public function getCurrencyPriceProduct(
        Product $product,
        Order $order = null,
        SpecialPriceList $specialPrice,
        $price
    )
    {
        $currency = Currency::getMainCurrency();
        if ($product->currency_id !== $currency->id) {
            $foreignCurrency = Currency::findById($product->currency_id);
            $price = $price / $foreignCurrency->convert_nominal * $foreignCurrency->convert_rate ;
        }
        return round($price, 2);
    }


    /***
     * @param Product $product
     * @param Order $order
     * @param $price
     * @return mixed
     */
    public static function getDiscountPriceProduct(
        Product $product,
        Order $order = null,
        SpecialPriceList $specialPrice,
        $price
    )
    {
        self::getAllDiscounts();
        if (isset(self::$_allDiscounts['products'])) {
            foreach (self::$_allDiscounts['products'] as $discount) {
                $discountFlag = false;
                foreach (Discount::getTypeObjects() as $discountTypeObject) {
                    $discountFlag = $discountTypeObject
                        ->checkDiscount(
                            $discount,
                            $product,
                            $order
                        );

                    if ($discountFlag === false) {
                        break;
                    }
                }

                if ($discountFlag === true) {
                    $oldPrice = $price;
                    $price = $discount->getDiscountPrice($oldPrice);
                }
            }
        }
        return $price;
    }


    public static function getDiscountPriceOrder(
        Order $order,
        SpecialPriceList $specialPrice,
        $price
    )
    {

        $discountPrice = 0;

        $discountObjects = SpecialPriceObject::findAll(
            [
                'special_price_list_id'=>$specialPrice->id,
                'object_model_id'=>$order->id
            ]
        );

        foreach ($discountObjects as $object) {
            $discountPrice += $object->price;
        }


        return $price + $discountPrice;
    }

    public static function getDeliveryPriceOrder(
        Order $order,
        SpecialPriceList $specialPrice,
        $price
    )
    {
        return $price;
    }

    private static function getAllDiscounts()
    {
        if (!self::$_allDiscounts) {
            $cacheKey = 'getAllDiscounts';
            if (!self::$_allDiscounts = Yii::$app->cache->get($cacheKey)) {
                $discounts = Discount::find()->all();
                foreach ($discounts as $discount) {
                    self::$_allDiscounts[$discount->appliance][] = $discount;
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    self::$_allDiscounts,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(Discount::className())
                            ]
                        ]
                    )
                );
            }

        }
        return self::$_allDiscounts;
    }


    public static function handleSaveDiscounts(OrderCalculateEvent $event)
    {
        self::getAllDiscounts();
        if (isset(self::$_allDiscounts['order_with_delivery'])) {
            foreach (self::$_allDiscounts['order_with_delivery'] as $discount) {
                $discountFlag = false;
                foreach (Discount::getTypeObjects() as $discountTypeObject) {
                    $discountFlag = $discountTypeObject
                        ->checkDiscount(
                            $discount,
                            null,
                            $event->order
                        );

                    if ($discountFlag === false) {
                        break;
                    }

                }

                $special_price_list_id = SpecialPriceList::find()->where(
                    [
                        'handler' => 'getDiscountPriceOrder',
                        'object_id' => $event->order->object->id
                    ]
                )
                    ->one()
                    ->id;


                if ($discountFlag === true) {
                    $oldPrice = $event->price;
                    $price = $discount->getDiscountPrice($oldPrice);
                    $discountPrice = $price - $oldPrice;

                    SpecialPriceObject::setObject(
                        $special_price_list_id,
                        $event->order->id,
                        $discountPrice
                    );


                } else {
                    SpecialPriceObject::deleteAll(
                        [
                            'special_price_list_id' =>  $special_price_list_id,
                            'object_model_id' =>  $event->order->id
                        ]
                    );
                }

            }

        }

    }



}