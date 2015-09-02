<?php

namespace app\modules\shop\models;

/**
 * Class AbstractOrderItemEntity
 * @package app\modules\shop\models
 */
class AbstractOrderItemEntity extends OrderItem
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->custom_name;
    }

    /**
     * @return null
     */
    public function getModel()
    {
        return null;
    }
}