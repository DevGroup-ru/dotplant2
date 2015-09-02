<?php


namespace app\modules\shop\components;

use app\modules\shop\models\AddonOrderItemIdentity;

/**
 * Class AddonOrderItemIdentityFactory
 * @package app\modules\shop\components
 */
class AddonOrderItemIdentityFactory implements AbstractOrderItemIdentityFactory
{
    /**
     * @param \app\modules\shop\models\OrderItem $model
     * @return AddonOrderItemIdentity|null
     */
    public static function getOrderItemIdentityByModel($model = null)
    {
        return AddonOrderItemIdentity::findOne($model->attributes);
    }

    /**
     * @param int $id
     * @return AddonOrderItemIdentity|null
     */
    public static function getOrderItemIdentityById($id)
    {
        return AddonOrderItemIdentity::findOne($id);
    }
}