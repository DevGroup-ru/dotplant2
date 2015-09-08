<?php

namespace app\modules\shop\components;

class CustomEntity extends AbstractEntity
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->orderItem->custom_name;
    }
}
