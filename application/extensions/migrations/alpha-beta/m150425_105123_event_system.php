<?php

use \app\modules\core\helpers\EventTriggeringHelper;
use yii\db\Schema;
use yii\db\Migration;

class m150425_105123_event_system extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%events}}', [
            'id' => Schema::TYPE_PK,
            'owner_class_name' => Schema::TYPE_STRING . ' NOT NULL',
            'event_name' => Schema::TYPE_STRING . ' NOT NULL',
            'event_class_name' => Schema::TYPE_STRING . ' NOT NULL',
            'selector_prefix' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
            'event_description' => Schema::TYPE_TEXT . ' NOT NULL',
            'documentation_link' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
        ], $tableOptions);

        $this->insert(
            '{{%events}}',
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'product_page_showed',
                'event_class_name' => 'app\\modules\\shop\\events\\ProductPageShowed',
                'event_description' => 'Product page is showed to user',
                'documentation_link' => '',
            ]
        );

        $this->insert(
            '{{%events}}',
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'product_showed_in_list',
                'event_class_name' => 'app\\modules\\shop\\events\\ProductShowedInList',
                'event_description' => 'Product is showed in product listing(shop/product/list)',
                'documentation_link' => '',
            ]
        );

        $this->insert(
            '{{%events}}',
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'product_category_listed',
                'event_class_name' => 'app\\modules\\shop\\events\\ProductCategoryListed',
                'event_description' => 'Category is listed by shop/product/list as last_category_id.',
                'documentation_link' => '',
            ]
        );

        $this->createIndex('event_class_name', '{{%events}}', ['event_class_name(50)'], true);

        $this->createTable('{{%event_handlers}}', [
            'id' => Schema::TYPE_PK,
            'event_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'handler_class_name' => Schema::TYPE_STRING . ' NOT NULL',
            'handler_function_name' => Schema::TYPE_STRING . ' NOT NULL',
            'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
            'non_deletable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            'triggering_type' => Schema::TYPE_STRING . ' NOT NULL',
        ], $tableOptions);

        $this->insert(
            '{{%event_handlers}}',
            [
                'event_id' => 1,
                'sort_order' => 1,
                'handler_class_name' => 'app\modules\shop\helpers\LastViewedProducts',
                'handler_function_name' => 'handleProductShowed',
                'non_deletable' => 1,
                'triggering_type' => EventTriggeringHelper::TYPE_APPLICATION,
            ]
        );

        $this->createIndex('by_event_active', '{{%event_handlers}}', ['event_id', 'is_active']);
    }

    public function down()
    {
        $this->dropTable('{{%events}}');
        $this->dropTable('{{%event_handlers}}');

    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
