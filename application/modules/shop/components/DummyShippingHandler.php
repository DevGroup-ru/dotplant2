<?php

namespace app\modules\shop\components;

class DummyShippingHandler extends AbstractShippingHandler
{
    /**
     * @inheritdoc
     */
    public function calculate($data = [])
    {
        return false;
    }

    public function setLastError($message)
    {
        $this->lastErrorMessage = $message;
    }

    public function getCartForm($form, $order)
    {
        return '';
    }
}
