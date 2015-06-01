<?php

use yii\db\Schema;
use yii\db\Migration;

class m150228_090542_multiple_warehouses extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable(
            '{{%warehouse}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'country_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'city_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'address' => Schema::TYPE_TEXT,
                'description' => Schema::TYPE_TEXT,
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'map_latitude' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'map_longitude' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'shipping_center' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'issuing_center' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'xml_id' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%warehouse_phone}}',
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'phone' => Schema::TYPE_STRING . ' NOT NULL',
                'name' => Schema::TYPE_STRING,
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%warehouse_email}}',
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'email' => Schema::TYPE_STRING . ' NOT NULL',
                'name' => Schema::TYPE_STRING,
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%warehouse_openinghours}}',
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'monday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'tuesday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'wednesday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'thursday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'friday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'saturday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'sunday' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'all_day' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0', // 24h
                'opens' => Schema::TYPE_STRING,
                'closes' => Schema::TYPE_STRING,
                'break_from' => Schema::TYPE_STRING,
                'break_to' => Schema::TYPE_STRING,

            ],
            $tableOptions
        );

        $this->createTable(
            '{{%country}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'iso_code' => Schema::TYPE_STRING, // ISO 3166-1 alpha-3
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'slug' => Schema::TYPE_STRING,
            ],
            $tableOptions
        );

        $this->insert(
            '{{%country}}',
            [
                'name' => 'Россия',
                'iso_code' => 'RUS',
                'sort_order' => 0,
                'slug' => 'rossiya',
            ]
        );

        $this->insert(
            '{{%country}}',
            [
                'name' => 'USA',
                'iso_code' => 'USA',
                'sort_order' => 1,
                'slug' => 'usa',
            ]
        );

        $this->createTable(
            '{{%city}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'slug' => Schema::TYPE_STRING,
                'country_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
            $tableOptions
        );

        $this->insert(
            '{{%city}}',
            [
                'name' => 'Москва',
                'slug' => 'moscow',
                'country_id' => 1,
            ]
        );
        $this->insert(
            '{{%city}}',
            [
                'name' => 'Санкт-Петербург',
                'slug' => 'spb',
                'country_id' => 1,
            ]
        );
        $this->insert(
            '{{%city}}',
            [
                'name' => 'New York',
                'slug' => 'ny',
                'country_id' => 2,
            ]
        );

        $this->createIndex('city_country', '{{%city}}', 'country_id');
        $this->createIndex('wh_country', '{{%warehouse}}', 'country_id');
        $this->createIndex('wh_city', '{{%warehouse}}', 'city_id');
        $this->createIndex('wh_phone', '{{%warehouse_phone}}', 'warehouse_id');
        $this->createIndex('wh_email', '{{%warehouse_email}}', 'warehouse_id');
        $this->createIndex('wh_hours', '{{%warehouse_openinghours}}', 'warehouse_id');

        $this->insert(
            '{{%warehouse}}',
            [
                'name' => 'Main warehouse',
                'country_id' => 1,
                'city_id' => 1,
                'address' => 'Kremlin',
            ]
        );

        $this->insert(
            '{{%warehouse_phone}}',
            [
                'name' => 'Sales',
                'warehouse_id' => 1,
                'phone' => '+7 (495) 123-45-67',
            ]
        );

        $this->insert(
            '{{%warehouse_email}}',
            [
                'name' => 'Sales',
                'warehouse_id' => 1,
                'email' => 'moscow@example.com',
            ]
        );

        $this->insert(
            '{{%warehouse_openinghours}}',
            [
                'warehouse_id' => 1,
                'monday' => 1,
                'tuesday' => 1,
                'wednesday' => 1,
                'thursday' => 1,
                'friday' => 1,
                'saturday' => 1,
                'sunday' => 1,
                'all_day' => 1,
                'opens' => '',
                'closes' => '',
                'break_from' => '12:00',
                'break_to' => '13:00',
            ]
        );

        $this->insert(
            '{{%warehouse}}',
            [
                'name' => 'Second warehouse',
                'country_id' => 2,
                'city_id' => 3,
                'address' => 'The WallStreet hidden warehouse',
            ]
        );

        $this->insert(
            '{{%warehouse_phone}}',
            [
                'name' => 'Sales',
                'warehouse_id' => 2,
                'phone' => '+1 800 1-WAREHOUSE-1',
            ]
        );

        $this->insert(
            '{{%warehouse_email}}',
            [
                'name' => 'Sales',
                'warehouse_id' => 2,
                'email' => 'nyc@example.com',
            ]
        );

        $this->insert(
            '{{%warehouse_openinghours}}',
            [
                'warehouse_id' => 2,
                'monday' => 1,
                'tuesday' => 1,
                'wednesday' => 1,
                'thursday' => 1,
                'friday' => 0,
                'saturday' => 0,
                'sunday' => 1,
                'all_day' => 0,
                'opens' => '9:00',
                'closes' => '22:00',
                'break_from' => '',
                'break_to' => '',
            ]
        );

        // product bindings

        $this->createTable(
            '{{%warehouse_product}}',
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'product_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'in_warehouse' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'reserved_count' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'sku' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
            ],
            $tableOptions
        );


        // migrate old data to new data
        $this->execute(
            'INSERT INTO {{%warehouse_product}} (warehouse_id, product_id, in_warehouse, reserved_count)
              SELECT 1, p.id, p.in_warehouse, p.reserved_count FROM {{%product}} p'
        );

        $this->createIndex('wh_pr', '{{%warehouse_product}}', ['warehouse_id', 'product_id'], true);

        $this->dropColumn(
            '{{%product}}',
            'in_warehouse'
        );

        $this->dropColumn(
            '{{%product}}',
            'reserved_count'
        );
    }

    public function down()
    {
        $this->dropTable('{{%warehouse}}');
        $this->dropTable('{{%warehouse_openinghours}}');
        $this->dropTable('{{%warehouse_email}}');
        $this->dropTable('{{%warehouse_phone}}');
        $this->dropTable('{{%country}}');
        $this->dropTable('{{%city}}');

        $this->addColumn('{{%product}}', 'in_warehouse', Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0');
        $this->addColumn('{{%product}}', 'reserved_count', Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0');

        $this->execute(
            'UPDATE {{%product}} p SET p.in_warehouse = (SELECT wp.in_warehouse FROM {{%warehouse_product}} wp WHERE wp.product_id = p.id)'
        );
        $this->execute(
            'UPDATE {{%product}} p SET p.in_warehouse = (SELECT wp.reserved_count FROM {{%warehouse_product}} wp WHERE wp.product_id = p.id)'
        );
        $this->dropTable('{{%warehouse_product}}');


    }
}
