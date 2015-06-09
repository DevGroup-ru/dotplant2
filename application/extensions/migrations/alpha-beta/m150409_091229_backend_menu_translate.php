<?php

use yii\db\Schema;
use yii\db\Migration;

class m150409_091229_backend_menu_translate extends Migration
{
    public function up()
    {
        $this->update(
            '{{%backend_menu}}',
            [
                'translation_category' => 'app'
            ],
            [
                'name' => [
                    'Shop',
                    'Products',
                    'Orders',
                    'Order statuses',
                    'Payment types',
                    'Shipping options',
                    'Categories groups',
                ]
            ]
        );

    }

    public function down()
    {
        $this->update(
            '{{%backend_menu}}',
            [
                'translation_category' => 'shop'
            ],
            [
                'name' => [
                    'Shop',
                    'Products',
                    'Orders',
                    'Order statuses',
                    'Payment types',
                    'Shipping options',
                    'Categories groups',
                ]
            ]
        );
    }

}
