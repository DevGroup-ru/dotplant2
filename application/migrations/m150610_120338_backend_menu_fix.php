<?php

use yii\db\Schema;
use yii\db\Migration;

class m150610_120338_backend_menu_fix extends Migration
{
    public function up()
    {
        $ordersMenuItem = \app\backend\models\BackendMenu::find()
            ->where([
                'name' => 'Orders',
                'route' => 'shop/backend-order/index',
            ])
            ->orderBy(['parent_id' => SORT_ASC])
            ->one();
        if (null !== $ordersMenuItem) {
            $ordersMenuItem->route = '';
            $ordersMenuItem->save();
        }

    }

    public function down()
    {
        echo "m150610_120338_backend_menu_fix cannot be reverted.\n";

        return false;
    }
}
