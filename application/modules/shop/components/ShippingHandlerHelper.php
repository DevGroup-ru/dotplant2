<?php

namespace app\modules\shop\components;

use app\modules\shop\models\ShippingOption;
use Yii;
use yii\helpers\Json;

class ShippingHandlerHelper
{
    /**
     * Create ShippingHandler by class name
     * @param string $handlerClass
     * @param array $handlerParams
     * @return AbstractShippingHandler
     */
    public static function createHandlerByClass($handlerClass, $handlerParams)
    {
        try {
            $handler = new $handlerClass($handlerParams);
        } catch (\Exception $e) {
            $handler = new DummyShippingHandler(
                [
                    'lastError' => Yii::t('app', $e->getMessage()),
                ]
            );
        }
        return $handler;
    }

    /**
     * Create ShippingHandler by shipping option id
     * @param integer $id
     * @return AbstractShippingHandler
     */
    public static function createHandlerByShippingOptionId($id)
    {
        /** @var ShippingOption $shippingOption */
        $shippingOption = ShippingOption::findOne($id);
        if ($shippingOption === null) {
            return new DummyShippingHandler(
                [
                    'lastError' => Yii::t('app', 'Shipping option not found'),
                ]
            );
        }
        return static::createHandlerByClass(
            $shippingOption->handler_class,
            Json::decode($shippingOption->handler_params)
        );
    }
}
