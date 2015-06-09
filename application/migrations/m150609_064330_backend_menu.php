<?php

use yii\db\Schema;
use yii\db\Migration;

class m150609_064330_backend_menu extends Migration
{
    public function up()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;

        $tblMenu = \app\backend\models\BackendMenu::tableName();
        $ordersMenuItem = \app\backend\models\BackendMenu::findOne([
            'name' => 'Orders',
            'route' => 'shop/backend-order/index',
        ]);
        /** @var \app\backend\models\BackendMenu $ordersMenuItem */
        if (null === $ordersMenuItem) {
            echo 'Where is the Orders menu item?';
            return false;
        }

        $this->batchInsert($tblMenu,
            [
                'parent_id',
                'name',
                'route',
                'icon',
                'sort_order',
                'added_by_ext',
                'rbac_check',
                'css_class',
                'translation_category'
            ],
            [
                [$ordersMenuItem->id, 'Orders', '/shop/backend-order/index', 'list-alt', 0, 'shop', 'order manage', '', 'app'],
                [$ordersMenuItem->id, 'Customers', '/shop/backend-customer/index', 'user', 1, 'shop', 'order manage', '', 'app'],
                [$ordersMenuItem->id, 'Contragents', '/shop/backend-contragent/index', 'user', 2, 'shop', 'order manage', '', 'app'],
            ]
        );
    }

    public function down()
    {
        echo "m150609_064330_backend_menu cannot be reverted.\n";
        return false;
    }
}
