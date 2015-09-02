<?php

namespace app\modules\shop\models;

/**
 * Class AbstractOrderItemIdentity
 * @package app\modules\shop\models
 */
class AbstractOrderItemIdentity extends OrderItem
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