<?php

use yii\db\Schema;
use yii\db\Migration;

class m150512_094543_discount extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%discount}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'appliance' => "ENUM('order_without_delivery','order_with_delivery','products','product_categories','delivery') NOT NULL",
                'value' => Schema::TYPE_FLOAT . ' NOT NULL',
                'value_in_percent' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'apply_order_price_lg' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT -1',
            ]
        );

        $this->createTable(
            '{{%discount_code}}',
            [
                'id' => Schema::TYPE_PK,
                'code' => Schema::TYPE_STRING . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'valid_from' => Schema::TYPE_TIMESTAMP,
                'valid_till' => Schema::TYPE_TIMESTAMP,
                'maximum_uses' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'used' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0'
            ]
        );

        $this->createTable(
            '{{%category_discount}}',
            [
                'id' => Schema::TYPE_PK,
                'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ]
        );

        $this->createTable(
            '{{%user_discount}}',
            [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ]
        );

        $this->createTable(
            '{{%order_discount}}',
            [
                'id' => Schema::TYPE_PK,
                'order_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'applied_date' => Schema::TYPE_TIMESTAMP
            ]
        );

        $this->createTable(
            '{{%product_discount}}',
            [
                'id' => Schema::TYPE_PK,
                'product_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ]
        );



        $this->createTable(
            '{{%discount_type}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'class' => Schema::TYPE_STRING . ' NOT NULL',
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'checking_class' => "ENUM('Order','OrderItem') NOT NULL",
                'sort_order' => Schema::TYPE_INTEGER .' NOT NULL DEFAULT 0'
            ]
        );

        $this->batchInsert(
            '{{%discount_type}}',
            [
                'name', 'class', 'checking_class'
            ],
            [
                [
                    'Discount Code', 'app\modules\shop\models\DiscountCode', 'Order'
                ],
                [
                    'Category Discount', 'app\modules\shop\models\CategoryDiscount', 'OrderItem'
                ],
                [
                    'User Discount', 'app\modules\shop\models\UserDiscount', 'Order'
                ],
                [
                    'Order Discount', 'app\modules\shop\models\OrderDiscount', 'Order'
                ],
                [
                    'Product Discount', 'app\modules\shop\models\ProductDiscount', 'OrderItem'
                ],
            ]
        );


    }

    public function down()
    {
        $this->dropTable('{{%discount}}');
        $this->dropTable('{{%discount_code}}');
        $this->dropTable('{{%category_discount}}');
        $this->dropTable('{{%user_discount}}');
        $this->dropTable('{{%order_discount}}');
        $this->dropTable('{{%product_discount}}');
        $this->dropTable('{{%discount_type}}');
    }

}
