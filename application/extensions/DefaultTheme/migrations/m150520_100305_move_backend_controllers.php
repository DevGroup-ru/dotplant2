<?php

use app\backend\models\BackendMenu;
use yii\db\Migration;

class m150520_100305_move_backend_controllers extends Migration
{
    public function up()
    {
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-category/index'],
            ['route' => 'backend/category/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-product/index'],
            ['route' => 'backend/product/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-order/index'],
            ['route' => 'backend/order/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-payment-type/index'],
            ['route' => 'backend/payment-type/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-shipping-option/index'],
            ['route' => 'backend/shipping-option/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-category-group/index'],
            ['route' => 'backend/category-group/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-currencies/index'],
            ['route' => 'backend/currencies/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-stage/index', 'name' => 'Stages'],
            ['route' => 'backend/order-status/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'shop/backend-prefiltered-pages/index'],
            ['route' => 'backend/prefiltered-pages/index']
        );
    }

    public function down()
    {
        echo "m150520_100305_move_backend_controllers cannot be reverted.\n";

        return false;
    }

}
