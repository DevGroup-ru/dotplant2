<?php

namespace app\modules\shop\components;

class FixedShippingCostHandler extends AbstractShippingHandler
{
    /**
     * @var false|int shipping option price
     */
    public $price = false;

    /**
     * @inheritdoc
     */
    public function calculate($data = [])
    {
        if ($this->price === false) {
            $this->lastErrorMessage = \Yii::t('app', 'Not set a price for this shipping option');
        }
        return $this->price;
    }

    /**
     * @inheritdoc
     */
    public function getCartForm($form, $order)
    {
        return '';
    }
}
