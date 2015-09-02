<?php


namespace app\modules\shop\components;

/**
 * Interface AbstractOrderItemEntityFactory
 * @package app\modules\shop\components
 */
interface AbstractOrderItemEntityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return \app\modules\shop\models\AbstractOrderItemEntity
     */
    public static function getOrderItemEntityByModel($model = null);

    /**
     * @param int $id
     * @return \app\modules\shop\models\AbstractOrderItemEntity
     */
    public static function getOrderItemEntityById($id);
}