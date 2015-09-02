<?php


namespace app\modules\shop\components;

use app\modules\shop\models\CustomOrderItemEntity;

/**
 * Class CustomOrderItemEntityFactory
 * @package app\modules\shop\components
 */
class CustomOrderItemEntityFactory implements AbstractOrderItemEntityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return CustomOrderItemEntity|null
     */
    public static function getOrderItemEntityByModel($model = null)
    {
        return CustomOrderItemEntity::findOne($model->attributes);
    }

    /**
     * @param int $id
     * @return CustomOrderItemEntity|null
     */
    public static function getOrderItemEntityById($id)
    {
        return CustomOrderItemEntity::findOne($id);
    }
}