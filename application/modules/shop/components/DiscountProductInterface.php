<?php

namespace app\modules\shop\components;


interface DiscountProductInterface
{

    public function checkProduct(DiscountInterface $object);

}