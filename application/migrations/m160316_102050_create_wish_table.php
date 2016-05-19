<?php

use yii\db\Migration;
use app\modules\shop\models\Wishlist;
use app\modules\shop\models\WishlistProduct;
use app\modules\user\models\User;
use app\modules\shop\models\Product;

class m160316_102050_create_wish_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(Wishlist::tableName(), [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string()->notNull(),
            'default' => $this->boolean()->notNull()->defaultValue(true),
        ]);

        $this->createTable(WishlistProduct::tableName(), [
            'id' => $this->primaryKey(),
            'wishlist_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->unsigned()->notNull(),
            'UNIQUE KEY `ix-wishlist_id-product_id` (`wishlist_id`, `product_id`)',
        ]);
        $this->addForeignKey('wishlist_product_wishlist_id', WishlistProduct::tableName(), 'wishlist_id', Wishlist::tableName(), 'id', 'CASCADE');
        $this->addForeignKey('wishlist_product_product_id', WishlistProduct::tableName(), 'product_id', Product::tableName(), 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable(WishlistProduct::tableName());
        $this->dropTable(Wishlist::tableName());
    }
}
