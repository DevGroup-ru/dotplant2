<?php


namespace app\modules\shop\components;

/**
 * Interface AbstractOrderItemIdentityFactory
 * @package app\modules\shop\components
 */
interface AbstractOrderItemIdentityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return \app\modules\shop\models\AbstractOrderItemIdentity
     */
    public static function getOrderItemIdentityByModel($model = null);

    /**
     * @param int $id
     * @return \app\modules\shop\models\AbstractOrderItemIdentity
     */
    public static function getOrderItemIdentityById($id);
}