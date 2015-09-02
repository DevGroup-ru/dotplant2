<?php


namespace app\modules\shop\components;

use app\modules\shop\models\ProductOrderItemIdentity;

/**
 * Class ProductOrderItemIdentityFactory
 * @package app\modules\shop\components
 */
class ProductOrderItemIdentityFactory implements AbstractOrderItemIdentityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return ProductOrderItemIdentity|null
     */
    public static function getOrderItemIdentityByModel($model = null)
    {
        return ProductOrderItemIdentity::findOne($model->attributes);
    }

    /**
     * @param int $id
     * @return ProductOrderItemIdentity|null
     */
    public static function getOrderItemIdentityById($id)
    {
        return ProductOrderItemIdentity::findOne($id);
    }
}