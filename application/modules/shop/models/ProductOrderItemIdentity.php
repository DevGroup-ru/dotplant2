<?php

namespace app\modules\shop\models;

/**
 * Class ProductOrderItemIdentity
 * @package app\modules\shop\models
 * Relations:
 * @property Product $model
 */
class ProductOrderItemIdentity extends AbstractOrderItemIdentity
{
    /**
     * @return string
     */
    public function getName()
    {
        if (empty($this->custom_name) === true) {
            return $this->model->name;
        } else {
            return $this->custom_name;
        }
    }

    /**
     * @return Product|null
     */
    public function getModel()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}