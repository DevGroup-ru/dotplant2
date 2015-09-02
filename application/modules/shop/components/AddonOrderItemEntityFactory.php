<?php


namespace app\modules\shop\components;

use app\modules\shop\models\AddonOrderItemEntity;

/**
 * Class AddonOrderItemEntityFactory
 * @package app\modules\shop\components
 */
class AddonOrderItemEntityFactory implements AbstractOrderItemEntityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return AddonOrderItemEntity|null
     */
    public static function getOrderItemEntityByModel($model = null)
    {
        return AddonOrderItemEntity::findOne($model->attributes);
    }

    /**
     * @param int $id
     * @return AddonOrderItemEntity|null
     */
    public static function getOrderItemEntityById($id)
    {
        return AddonOrderItemEntity::findOne($id);
    }
}