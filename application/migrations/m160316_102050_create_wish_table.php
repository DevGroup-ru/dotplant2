<?php

use yii\db\Migration;
use yii\db\Schema;

class m160316_102050_create_wish_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%wishlist}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'default' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'1\'',
        ]);

        $this->createTable('{{%wishlist_product}}', [
            'id' => Schema::TYPE_PK,
            'wishlist_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'product_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'UNIQUE KEY `ix-wishlist_id-product_id` (`wishlist_id`, `product_id`)',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%wishlist}}');
        $this->dropTable('{{%wishlist_product}}');
    }
}
