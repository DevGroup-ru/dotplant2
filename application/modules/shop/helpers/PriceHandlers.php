<?php
namespace app\modules\shop\helpers;

use app\modules\shop\events\OrderCalculateEvent;
use app\modules\shop\models\AbstractDiscountType;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Discount;
use app\modules\shop\models\Order;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\models\SpecialPriceObject;
use Yii;

class PriceHandlers
{
    /**
     * @var array|null
     */
    static protected $discountsByAppliance = null;

    /**
     * @param Order $order
     * @return float
     */
    static public function getFinalDeliveryPrice(Order $order)
    {
        $finalCost = $order->shippingOption->cost;
        foreach ($order->specialPriceObjects as $spo) {
            if ($spo->isDiscount()) {
                $finalCost += $spo->price;
            }
        }

        return $finalCost;
    }

    /**
     * @param Product $product
     * @param Order|null $order
     * @param SpecialPriceList $specialPrice
     * @param int $price
     * @param int $quantity
     * @return float
     */
    static public function getCurrencyPriceProduct(
        Product $product,
        Order $order = null,
        SpecialPriceList $specialPrice = null,
        $price = 0,
        $quantity = 1
    ) {
        return CurrencyHelper::convertToMainCurrency($price, $product->currency);
    }


    /***
     * @param Product $product
     * @param null|Order $order
     * @param SpecialPriceList $specialPrice
     * @param int $price
     * @param int $quantity
     * @return mixed
     */
    static public function getDiscountPriceProduct(
        Product $product,
        Order $order = null,
        SpecialPriceList $specialPrice = null,
        $price = 0,
        $quantity = 1
    ) {
        static $discounts = null;
        if (null === $discounts) {
            $discounts = self::getDiscountsByAppliance(['products']);
        }

        foreach ($discounts as $discount) {
            /** @var Discount $discount */
            $discountFlag = 0;
            foreach (Discount::getTypeObjects() as $discountTypeObject) {
                /** @var AbstractDiscountType $discountTypeObject */
                if (true === $discountTypeObject->checkDiscount($discount, $product, $order)) {
                    $discountFlag++;
                }
            }

            if ($discountFlag > 0) {
                $oldPrice = $price;
                $price = $discount->getDiscountPrice($oldPrice);
            }
        }

        return $price;
    }

    /**
     * @param Order $order
     * @param SpecialPriceList $specialPrice
     * @param $price
     * @return float
     */
    static public function getDiscountPriceOrder(
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

    /**
     * @param Order $order
     * @param SpecialPriceList $specialPrice
     * @param $price
     * @return float
     */
    static public function getDeliveryPriceOrder(
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
     * @param array $types
     * @return array
     */
    static protected function getDiscountsByAppliance($types = [])
    {
        if (true === empty($types)) {
            return [];
        }

        if (null === static::$discountsByAppliance) {
            static::$discountsByAppliance = [];
            foreach (Discount::find()->all() as $model) {
                /** @var Discount $model */
                static::$discountsByAppliance[$model->appliance][$model->id] = $model;
            }
        }

        $result = [];

        foreach ($types as $type) {
            if (true === isset(static::$discountsByAppliance[$type])) {
                $result = array_merge($result, static::$discountsByAppliance[$type]);
            }
        }

        return $result;
    }

    /**
     * @param OrderCalculateEvent $event
     * @return null
     */
    static public function handleSaveDiscounts(OrderCalculateEvent $event)
    {
        if (OrderCalculateEvent::BEFORE_CALCULATE !== $event->state) {
            return null;
        }

        static $discounts = null;
        if (null === $discounts) {
            $discounts = self::getDiscountsByAppliance(['order_without_delivery', 'order_with_delivery', 'delivery']);
        }

        foreach ($discounts as $discount) {
            /** @var Discount $discount */
            $discountFlag = 0;
            foreach (Discount::getTypeObjects() as $discountTypeObject) {
                /** @var AbstractDiscountType $discountTypeObject */
                if (true === $discountTypeObject->checkDiscount($discount, null, $event->order)) {
                    $discountFlag++;
                }
            }

            $special_price_list_id = SpecialPriceList::find()
            ->where([
                'handler' => 'getDiscountPriceOrder',
                'object_id' => $event->order->object->id
            ])
            ->one()
            ->id;

            if ($discountFlag > 0
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

            }
            /** Данный кусок удаляет все объекты, если одна из скидок не применилась! */
            /* else {
                SpecialPriceObject::deleteAll([
                    'special_price_list_id' => $special_price_list_id,
                    'object_model_id' => $event->order->id
                ]);
            } */
        }
    }

    /**
     * @param OrderCalculateEvent $event
     * @return null
     */
    static public function handleSaveDelivery(OrderCalculateEvent $event)
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
        }
        /** Сомнительно! */
        /* else {
            SpecialPriceObject::deleteAll(
                [
                    'special_price_list_id' => $special_price_list->id,
                    'object_model_id' => $event->order->id
                ]
            );
        } */
    }
}
