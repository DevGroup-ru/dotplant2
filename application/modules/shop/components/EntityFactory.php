<?php
namespace app\modules\shop\components;

use app\modules\shop\models\OrderItem;

class EntityFactory
{
    /**
     * @param OrderItem $model
     * @return AbstractEntity
     */
    public static function getEntityByModel(OrderItem $model)
    {
        if (false === empty($model->product_id)) {
            return new ProductEntity($model);
        } elseif (false === empty($model->addon_id)) {
            return new AddonEntity($model);
        }

        return new CustomEntity($model);
    }
}
