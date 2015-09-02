<?php


namespace app\modules\shop\components;

use app\modules\shop\models\CustomOrderItemIdentity;

/**
 * Class CustomOrderItemIdentityFactory
 * @package app\modules\shop\components
 */
class CustomOrderItemIdentityFactory implements AbstractOrderItemIdentityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return CustomOrderItemIdentity|null
     */
    public static function getOrderItemIdentityByModel($model = null)
    {
        return CustomOrderItemIdentity::findOne($model->attributes);
    }

    /**
     * @param int $id
     * @return CustomOrderItemIdentity|null
     */
    public static function getOrderItemIdentityById($id)
    {
        return CustomOrderItemIdentity::findOne($id);
    }
}