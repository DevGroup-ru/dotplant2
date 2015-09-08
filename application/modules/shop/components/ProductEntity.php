<?php

namespace app\modules\shop\components;

use app\modules\shop\models\OrderItem;

class ProductEntity extends AbstractEntity
{
    /**
     * @param OrderItem $orderItem
     */
    public function __construct($orderItem)
    {
        parent::__construct($orderItem);
        $this->model = $orderItem->product;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return !empty($this->orderItem->custom_name) || $this->model === null
            ? $this->orderItem->custom_name
            : $this->model->name;
    }
}
