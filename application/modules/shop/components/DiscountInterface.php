<?php

namespace app\modules\shop\components;


use app\modules\shop\models\Order;
use app\modules\user\models\User;

interface DiscountInterface
{
    /**
     * @return array
     */
    public function getFilters();

    /**
     * @return Order
     */
    public function getOrder();

    /**
     * @return User
     */
    public function getUser();

    /**
     * @return float
     */
    public function getOrderDiscount();

    /**
     * @return float
     */
    public function getProductDiscount($id_product);

}