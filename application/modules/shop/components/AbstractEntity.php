<?php
namespace app\modules\shop\components;

use app\modules\shop\models\OrderItem;

abstract class AbstractEntity
{
    /**
     * @property $orderItem OrderItem
     * @property $model
     */
    public $orderItem;
    public $model;

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param OrderItem $model
     */
    public function __construct(OrderItem $model)
    {
        $this->orderItem = $model;

        $this->init();
    }

    /**
     */
    public function init()
    {
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }
}
