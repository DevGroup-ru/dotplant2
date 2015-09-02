<?php


namespace app\modules\shop\components;

use app\modules\shop\models\ProductOrderItemEntity;

/**
 * Class ProductOrderItemEntityFactory
 * @package app\modules\shop\components
 */
class ProductOrderItemEntityFactory implements AbstractOrderItemEntityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return ProductOrderItemEntity|null
     */
    public static function getOrderItemEntityByModel($model = null)
    {
        return ProductOrderItemEntity::findOne($model->attributes);
    }

    /**
     * @param int $id
     * @return ProductOrderItemEntity|null
     */
    public static function getOrderItemEntityById($id)
    {
        return ProductOrderItemEntity::findOne($id);
    }
}