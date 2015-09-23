<?php

namespace app\modules\shop\helpers;


use app\modules\shop\events\OrderCalculateEvent;
use app\modules\shop\models\Currency;
use app\modules\shop\models\DeliveryInformation;
use app\modules\shop\models\Discount;
use app\modules\shop\models\Order;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\models\SpecialPriceObject;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

class PriceHandlers
{
    private static $allDiscounts = [];

    public static function getFinalDeliveryPrice(Order $order)
    {
        $finalCost = $order->shippingOption->cost;
        foreach ($order->specialPriceObjects as $spo) {
            if ($spo->isDiscount()) {
                $finalCost += $spo->price;
            }
        }

        return $finalCost;
    }


    public static function getCurrencyPriceProduct(
        Product $product,
        Order $order = null,
        SpecialPriceList $specialPrice,
        $price
    ) {
        $currency = Currency::getMainCurrency();
        if ($product->currency_id !== $currency->id) {
            $foreignCurrency = Currency::findById($product->currency_id);
            $price = $price / $foreignCurrency->convert_nominal * $foreignCurrency->convert_rate;
        }
        return round($price, 2);
    }


    /***
     * @param Product $product
     * @param null|Order $order
     * @param $price
     * @return mixed
     */
    public static function getDiscountPriceProduct(
        Product $product,
        Order $order = null,
        SpecialPriceList $specialPrice,
        $price
    ) {
        self::getAllDiscounts();
        if (isset(self::$allDiscounts['products'])) {
            foreach (self::$allDiscounts['products'] as $discount) {
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
    ) {

        $discountPrice = 0;

        $discountObjects = SpecialPriceObject::findAll(
            [
                'special_price_list_id' => $specialPrice->id,
                'object_model_id' => $order->id
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
    ) {
        $deliveryPrice = 0;

        $deliveryObjects = SpecialPriceObject::findAll([
            'special_price_list_id' => $specialPrice->id,
            'object_model_id' => $order->id
        ]);

        foreach ($deliveryObjects as $object) {
            $deliveryPrice += $object->price;
        }

        return $price + $deliveryPrice;
    }

    /**
     * @return array|mixed
     */
    private static function getAllDiscounts()
    {
        if (self::$allDiscounts === []) {
            $cacheKey = 'getAllDiscounts';
            if (!self::$allDiscounts = Yii::$app->cache->get($cacheKey)) {
                self::$allDiscounts = [];
                $discounts = Discount::find()->all();
                foreach ($discounts as $discount) {
                    self::$allDiscounts[$discount->appliance][] = $discount;
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    self::$allDiscounts,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(Discount::className())
                        ]
                    ])
                );
            }
        }
        return self::$allDiscounts;
    }


    public static function handleSaveDiscounts(OrderCalculateEvent $event)
    {
        if (OrderCalculateEvent::BEFORE_CALCULATE !== $event->state) {
            return null;
        }

        self::getAllDiscounts();
        foreach (self::$allDiscounts as $discountTypeName => $discountType) {
            if (in_array($discountTypeName, ['order_without_delivery', 'order_with_delivery', 'delivery'])) {
                foreach ($discountType as $discount) {
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

                    $special_price_list_id = SpecialPriceList::find()
                    ->where([
                        'handler' => 'getDiscountPriceOrder',
                        'object_id' => $event->order->object->id
                    ])
                    ->one()
                    ->id;

                    if ($discountFlag === true
                        && $event->price > 0
                        && (
                            $discount->apply_order_price_lg !== -1
                            && $event->order->total_price > $discount->apply_order_price_lg
                        )
                    ) {
                        $oldPrice = $event->price;
                        $deliveryPrice = SpecialPriceObject::getSumPrice(
                            $event->order->id,
                            SpecialPriceList::TYPE_DELIVERY
                        );
                        $price = $discount->getDiscountPrice($oldPrice, $deliveryPrice);
                        $discountPrice = $price - $oldPrice;

                        SpecialPriceObject::setObject(
                            $special_price_list_id,
                            $event->order->id,
                            $discountPrice,
                            $discount->name
                        );

                    } else {
                        SpecialPriceObject::deleteAll([
                            'special_price_list_id' => $special_price_list_id,
                            'object_model_id' => $event->order->id
                        ]);
                    }
                }
            }

        }

    }

    public static function handleSaveDelivery(OrderCalculateEvent $event)
    {
        if (OrderCalculateEvent::BEFORE_CALCULATE !== $event->state) {
            return null;
        }

        $deliveryInformation = $event->order->orderDeliveryInformation;
        $shippingOption = $event->order->shippingOption;
        $special_price_list = SpecialPriceList::find()->where(
            [
                'handler' => 'getDeliveryPriceOrder',
                'object_id' => $event->order->object->id
            ]
        )->one();

        if (null !== $deliveryInformation && null !== $shippingOption) {
            SpecialPriceObject::setObject(
                $special_price_list->id,
                $event->order->id,
                $shippingOption->cost,
                $shippingOption->name
            );
        } else {
            SpecialPriceObject::deleteAll(
                [
                    'special_price_list_id' => $special_price_list->id,
                    'object_model_id' => $event->order->id
                ]
            );
        }

    }
}