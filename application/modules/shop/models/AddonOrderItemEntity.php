<?php


namespace app\modules\shop\models;

/**
 * Class AddonOrderItemEntity
 * @package app\modules\shop\models
 * Relations:
 * @property Addon $model
 */
class AddonOrderItemEntity extends AbstractOrderItemEntity
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
     * @return Addon|null
     */
    public function getModel()
    {
        return $this->hasOne(Addon::className(), ['id' => 'addon_id']);
    }
}