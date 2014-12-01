<?php

use yii\db\Schema;
use yii\db\Migration;

class m141201_114400_product_listing_sort extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%product_listing_sort}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'sort_field' => 'VARCHAR(255) NOT NULL',
                'asc_desc' => 'ENUM(\'asc\',\'desc\') NOT NULL DEFAULT \'asc\'',
                'enabled' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 1',
                'sort_order' => 'INT UNSIGNED DEFAULT 0',
            ],
            $tableOptions
        );
        $this->batchInsert(
            '{{%product_listing_sort}}',
            [
                'name',
                'sort_field',
                'asc_desc',
                'enabled',
                'sort_order',
            ],
            [
                ['Popularity', 'product.sort_order', 'asc', 1, 0],
                ['Price 0-9', 'product.price', 'asc', 1, 1],
                ['Price 9-0', 'product.price', 'desc', 1, 2],
                ['Name', 'product.name', 'asc', 1, 3],
                ['Name', 'product.name', 'desc', 1, 4],

            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%product_listing_sort}}');
    }
}
