<?php

namespace app\modules\shop\components;

use app\modules\shop\models\OrderItem;

abstract class AbstractEntity
{
    /** @var  OrderItem */
    public $orderItem;
    public $model;

    abstract public function getName();

    public function __construct($orderItem)
    {
        $this->orderItem = $orderItem;
    }

    public function getModel()
    {
        return $this->model;
    }
}
