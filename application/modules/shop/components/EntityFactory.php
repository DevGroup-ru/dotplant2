<?php

namespace app\modules\shop\components;

use app\modules\shop\models\OrderItem;
use yii\base\Exception;

class EntityFactory
{
    /**
     * @param OrderItem $orderItem
     * @throws Exception
     */
    public static function getEntityByModel($orderItem)
    {
        if ($orderItem === null) {
            throw new Exception('Order item cannot be null');
        }
        if (!empty($orderItem->product_id)) {
            return new ProductEntity($orderItem);
        } elseif (!empty($orderItem->addon_id)) {
            return new AddonEntity($orderItem);
        } else {
            return new CustomEntity($orderItem);
        }
    }
}