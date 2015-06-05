<?php

use app\backend\models\ApiService;
use app\backend\models\Notification;
use app\backend\models\OrderChat;
use app\components\Helper;
use app\modules\shop\models\Category;
use app\modules\shop\models\CategoryGroup;
use app\modules\shop\models\CategoryGroupRouteTemplates;
use app\models\DynamicContent;
use app\models\ErrorLog;
use app\models\ErrorUrl;
use app\models\Form;
use app\modules\image\models\Image;
use app\models\Layout;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderTransaction;
use app\modules\page\models\Page;
use app\modules\shop\models\PaymentType;
use app\modules\shop\models\Product;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyHandler;
use app\models\PropertyStaticValues;
use app\models\Route;
use app\modules\shop\models\ShippingOption;
use app\models\Submission;
use app\models\SubscribeEmail;
use app\modules\review\models\Review;
use app\modules\user\models\User;
use app\modules\user\models\UserService;
use app\models\View;
use app\models\ViewObject;
use app\widgets\navigation\models\Navigation;
use yii\db\Migration;
use yii\helpers\Json;

class m141023_084857_init extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        // Schemes
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            ApiService::tableName(),
            [
                'service_id' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'access_token' => 'VARCHAR(255) NOT NULL',
                'token_type' => 'VARCHAR(255) NOT NULL ',
                'expires_in' => 'INT UNSIGNED NOT NULL ',
                'create_ts' => 'INT UNSIGNED NOT NULL ',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%session}}',
            [
                'id' => 'char(40) NOT NULL PRIMARY KEY',
                'expire' => 'INT DEFAULT NULL',
                'data' => 'BLOB',
            ],
            $tableOptions
        );
        $this->createTable(
            Image::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'filename' => 'VARCHAR(255) NOT NULL',
                'image_src' => 'VARCHAR(255) NOT NULL',
                'thumbnail_src' => 'VARCHAR(255) DEFAULT NULL',
                'image_description' => 'VARCHAR(255) NOT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'KEY `ix-image-object_id-object_model_id` (`object_id`, `object_model_id`)',
                'KEY `ix-image-filename` (`filename`)',
            ],
            $tableOptions
        );
        $this->createTable(
            Navigation::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'parent_id' => 'INT UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'url' => 'VARCHAR(255)',
                'route' => 'VARCHAR(255)',
                'route_params' => 'TEXT DEFAULT NULL',
                'advanced_css_class' => 'VARCHAR(255)',
                'sort_order' => 'INT DEFAULT \'0\'',
                'KEY `ix-navigation-parent_id` (`parent_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            DynamicContent::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'route' => 'VARCHAR(255) DEFAULT NULL',
                'name' => 'VARCHAR(255) DEFAULT NULL',
                'content_block_name' => 'VARCHAR(80) DEFAULT \'bottom_text\'',
                'content' => 'TEXT DEFAULT NULL',
                'append_content' => 'TINYINT DEFAULT \'0\'',
                'title' => 'VARCHAR(255)',
                'append_title' => 'TINYINT DEFAULT \'0\'',
                'h1' => 'VARCHAR(255) DEFAULT NULL',
                'append_h1' => 'TINYINT DEFAULT \'0\'',
                'meta_description' => 'VARCHAR(255) DEFAULT NULL',
                'append_meta_description' => 'TINYINT DEFAULT \'0\'',
                'apply_if_last_category_id' => 'INT UNSIGNED DEFAULT NULL',
                'apply_if_params' => 'TEXT DEFAULT NULL',
                'object_id' => 'INT UNSIGNED DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            Object::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'object_class' => 'VARCHAR(255) NOT NULL',
                'object_table_name' => 'VARCHAR(255) NOT NULL',
                'column_properties_table_name' => 'VARCHAR(255) NOT NULL',
                'eav_table_name' => 'VARCHAR(255) NOT NULL',
                'categories_table_name' => 'VARCHAR(255) NOT NULL',
                'link_slug_category' => 'VARCHAR(255) NOT NULL',
                'link_slug_static_value' => 'VARCHAR(255) NOT NULL',
                'object_slug_attribute' => 'VARCHAR(255) NOT NULL',
                'KEY `ix-object-object_class` (`object_class`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ObjectPropertyGroup::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'property_group_id' => 'INT UNSIGNED NOT NULL',
                'KEY `ix-object_property_group-object_id-object_model_id` (`object_id`,`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ObjectStaticValues::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'property_static_value_id' => 'INT UNSIGNED NOT NULL',
                'KEY `ix-object-static-values-object_id-object_model_id` (`object_id`, `object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            PropertyGroup::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_id' => 'INT UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'is_internal' => 'TINYINT DEFAULT \'0\'',
                'hidden_group_title' => 'TINYINT DEFAULT \'0\'',
                'KEY `ix-property_group-object_id` (`object_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            PropertyHandler::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'frontend_render_view' => 'VARCHAR(255) NOT NULL',
                'frontend_edit_view' => 'VARCHAR(255) NOT NULL',
                'backend_render_view' => 'VARCHAR(255) NOT NULL',
                'backend_edit_view' => 'VARCHAR(255) NOT NULL',
                'handler_class_name' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            PropertyStaticValues::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'property_id' => 'INT UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'value' => 'VARCHAR(255) NOT NULL',
                'slug' => 'VARCHAR(255) NOT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'title_append' => 'VARCHAR(255) DEFAULT NULL',
                'KEY `ix-property_static_values-property_id` (`property_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%product_category_full_slug}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_id' => 'INT UNSIGNED NOT NULL',
                'full_slug_id' => 'INT UNSIGNED NOT NULL',
                'KEY `category_id` (`category_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%product_static_value_full_slug}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'full_slug_id' => 'INT UNSIGNED NOT NULL',
                'property_static_value_id' => 'INT UNSIGNED NOT NULL',
                'KEY `property_static_value_id` (`property_static_value_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%product_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INTEGER DEFAULT \'0\'',
                'KEY  `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            Route::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'route' => 'VARCHAR(255) NOT NULL',
                'url_template' => 'TEXT',
                'object_id' => 'INT UNSIGNED DEFAULT NULL',
                'name' => 'VARCHAR(255)',
            ],
            $tableOptions
        );
        $this->createTable(
            Property::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'property_group_id' => 'INT UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'key' => 'VARCHAR(20) DEFAULT \'\'',
                'value_type' => 'ENUM(\'STRING\',\'NUMBER\') DEFAULT \'STRING\'',
                'property_handler_id' => 'INT UNSIGNED NOT NULL',
                'has_static_values' => 'TINYINT DEFAULT \'0\'',
                'has_slugs_in_values' => 'TINYINT DEFAULT \'0\'',
                'is_eav' => 'TINYINT DEFAULT \'0\'',
                'is_column_type_stored' => 'TINYINT DEFAULT \'0\'',
                'multiple' => 'TINYINT DEFAULT \'0\'',
                'sort_order' => 'INT DEFAULT \'0\'',
                'handler_additional_params' => 'TEXT NOT NULL',
                'display_only_on_depended_property_selected' => 'TINYINT DEFAULT \'0\'',
                'depends_on_property_id' => 'INT DEFAULT \'0\'',
                'depended_property_values' => 'TEXT DEFAULT NULL',
                'depends_on_category_group_id' => 'INT DEFAULT \'0\'',
                'hide_other_values_if_selected' => 'TINYINT DEFAULT \'0\'',
                'KEY `ix-property-property_group_id` (`property_group_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            Product::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'main_category_id' => 'INT UNSIGNED NOT NULL',
                'parent_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'option_generate' => 'TEXT DEFAULT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'title' => 'VARCHAR(255) DEFAULT NULL',
                'h1' => 'VARCHAR(255) DEFAULT NULL',
                'meta_description' => 'VARCHAR(255) DEFAULT NULL',
                'breadcrumbs_label' => 'VARCHAR(255) DEFAULT NULL',
                'slug' => 'VARCHAR(80) DEFAULT \'\'',
                'slug_compiled' => 'VARCHAR(180) DEFAULT \'\'',
                'slug_absolute' => 'TINYINT DEFAULT \'0\'',
                'content' => 'TEXT DEFAULT NULL',
                'announce' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'active' => 'TINYINT DEFAULT \'1\'',
                'price' => 'FLOAT UNSIGNED DEFAULT \'0\'',
                'old_price' => 'FLOAT UNSIGNED DEFAULT \'0\'',
                'is_deleted' => 'TINYINT UNSIGNED DEFAULT \'0\'',
                'KEY `ix-product-active-slug` (`active`, `slug`)',
                'KEY `ix-product-parent_id` (`parent_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%product_property}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            Layout::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'layout' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            View::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'view' => 'TEXT NOT NULL',
                'category' => 'VARCHAR(255) DEFAULT NULL',
                'internal_name' => 'VARCHAR(255) DEFAULT NULL',
                'KEY `ix-view-internal_name-category` (`internal_name`, `category`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ViewObject::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'view_id' => 'INT UNSIGNED NOT NULL',
                'UNIQUE KEY `uq-view_object-object_id-object_model_id` (`object_id`, `object_model_id`)'
            ],
            $tableOptions
        );
        $this->createTable(
            Page::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'parent_id' => 'INT UNSIGNED NOT NULL',
                'slug' => 'VARCHAR(80) NOT NULL',
                'slug_compiled' => 'VARCHAR(180) NOT NULL DEFAULT \'\'',
                'slug_absolute' => 'TINYINT(1) UNSIGNED DEFAULT \'0\'',
                'content' => 'LONGTEXT',
                'show_type' => "ENUM('show','list') DEFAULT 'show'",
                'published' => 'TINYINT(1) UNSIGNED DEFAULT \'1\'',
                'searchable' => 'TINYINT(1) UNSIGNED DEFAULT \'1\'',
                'robots' => 'TINYINT(3) UNSIGNED DEFAULT \'3\'',
                'title' => 'TEXT NOT NULL',
                'h1' => 'TEXT DEFAULT NULL',
                'meta_description' => 'TEXT DEFAULT NULL',
                'breadcrumbs_label' => 'TEXT DEFAULT NULL',
                'announce' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'date_added' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'date_modified' => 'TIMESTAMP NULL',
                'is_deleted' => 'TINYINT UNSIGNED DEFAULT \'0\'',
                'KEY `ix-page-slug_compiled-published` (`slug_compiled`, `published`)',
                'KEY `ix-page-parent_id` (`parent_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%page_property}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%page_category}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%page_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INTEGER DEFAULT \'0\'',
                'KEY `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            CategoryGroup::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            Category::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_group_id' => 'INT UNSIGNED NOT NULL',
                'parent_id' => 'INT UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'title' => 'VARCHAR(255) NOT NULL',
                'h1' => 'VARCHAR(255) NOT NULL',
                'meta_description' => 'VARCHAR(255) DEFAULT NULL',
                'breadcrumbs_label' => 'VARCHAR(255) DEFAULT NULL',
                'slug' => 'VARCHAR(80) DEFAULT \'\'',
                'slug_compiled' => 'VARCHAR(180) DEFAULT \'\'',
                'slug_absolute' => 'TINYINT DEFAULT \'0\'',
                'content' => 'TEXT DEFAULT NULL',
                'announce' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'title_append' => 'VARCHAR(255) DEFAULT NULL',
                'is_deleted' => 'TINYINT UNSIGNED DEFAULT \'0\'',
                'active' => 'TINYINT UNSIGNED DEFAULT \'1\'',
                'KEY `ix-category-category_group_id` (`category_group_id`, `parent_id`)',
                'KEY `ix-category-parent_id` (`parent_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%category_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'KEY `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%property_category}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%product_category}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
            ],
            $tableOptions
        );
        $this->createTable(
            CategoryGroupRouteTemplates::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_group_id' => 'int(11) unsigned NOT NULL',
                'route_id' => 'INT UNSIGNED NOT NULL',
                'template_json' => 'TEXT NOT NULL',
                'KEY `ix-category-group-route-templates-category_group_id-route_id` (`category_group_id`, `route_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            SubscribeEmail::tableName(),
            [
                'id' => 'INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'email' => 'VARCHAR(100) DEFAULT NULL',
                'name' => 'VARCHAR(50) DEFAULT NULL',
                'is_active' => 'TINYINT DEFAULT \'0\'',
                'last_notify' => 'INTEGER DEFAULT \'0\'',
            ],
            $tableOptions
        );
        $this->createTable(
            Order::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'user_id' => 'INT UNSIGNED NOT NULL',
                'manager_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'start_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'end_date' => "TIMESTAMP NULL",
                'cart_forming_time' => 'INT DEFAULT \'0\'',
                'order_status_id' => 'INT UNSIGNED NOT NULL',
                'shipping_option_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'payment_type_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'external_id' => 'VARCHAR(38) DEFAULT NULL',
                'items_count' => 'INT UNSIGNED DEFAULT \'0\'',
                'total_price' => 'FLOAT DEFAULT \'0\'',
                'hash' => 'CHAR(32) NOT NULL',
                'KEY `ix-order-user_id` (`user_id`)',
                'KEY `ix-order-manager_id` (`manager_id`)',
                'UNIQUE KEY `uq-order-hash` (`hash`)',
            ],
            $tableOptions
        );
        $this->createTable(
            OrderItem::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'order_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'product_id' => 'INT UNSIGNED NOT NULL',
                'quantity' => 'INT UNSIGNED DEFAULT \'1\'',
                'KEY `ix-order_item-order_id` (`order_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            OrderTransaction::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'order_id' => 'INT UNSIGNED NOT NULL',
                'payment_type_id' => 'INT UNSIGNED NOT NULL',
                'start_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'end_date' => 'TIMESTAMP NULL',
                'status' => 'TINYINT UNSIGNED NOT NULL',
                'total_sum' => 'DECIMAL(10, 2) NOT NULL',
                'params' => 'TEXT',
                'result_data' => 'TEXT',
                'KEY `ix-order_transaction-order_id` (`order_id`)'
            ],
            $tableOptions
        );
        $this->createTable(
            OrderChat::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'order_id' => 'INTEGER UNSIGNED NOT NULL',
                'user_id' => 'INTEGER UNSIGNED NOT NULL',
                'date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'message' => 'TEXT DEFAULT NULL',
                'KEY `ix-order_chat-order_id` (`order_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ShippingOption::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'description' => 'VARCHAR(255) NOT NULL',
                'price_from' => 'FLOAT DEFAULT 0',
                'price_to' => 'FLOAT DEFAULT 0',
                'cost' => 'FLOAT DEFAULT 0',
                'sort' => 'INT DEFAULT \'0\'',
                'active' => 'TINYINT DEFAULT \'0\'',
                'KEY `ix-shipping_option-active` (`active`)',
            ],
            $tableOptions
        );
        $this->createTable(
            PaymentType::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'class' => 'VARCHAR(255) NOT NULL',
                'params' => 'TEXT DEFAULT NULL',
                'logo' => 'VARCHAR(255) DEFAULT NULL',
                'commission' => 'FLOAT DEFAULT \'0\'',
                'active' => 'TINYINT DEFAULT \'0\'',
                'payment_available' => 'TINYINT DEFAULT \'1\'',
                'sort' => 'TINYINT DEFAULT \'1\'',
                'KEY `ix-payment_type-active` (`active`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%order_property}}',
            [
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%order_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INTEGER DEFAULT \'0\'',
                'KEY `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%order_category}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_id' => 'INTEGER UNSIGNED NOT NULL',
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            Form::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'form_view' => 'VARCHAR(255) DEFAULT NULL',
                'form_success_view' => 'VARCHAR(255) DEFAULT NULL',
                'email_notification_addresses' => 'TEXT NOT NULL',
                'email_notification_view' => 'VARCHAR(255) DEFAULT NULL',
                'form_open_analytics_action_id' => 'INT UNSIGNED DEFAULT NULL',
                'form_submit_analytics_action_id' => 'INT UNSIGNED DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%form_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT',
                'sort_order' => 'INTEGER DEFAULT \'0\'',
                'KEY  `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%form_property}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            Submission::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'form_id' => 'INT UNSIGNED NOT NULL',
                'date_received' => 'DATETIME DEFAULT NULL',
                'ip' => 'VARCHAR(255) DEFAULT NULL',
                'user_agent' => 'VARCHAR(255) DEFAULT NULL',
                'piwik_visitor_id' => 'VARCHAR(255) DEFAULT NULL',
                'additional_information' => 'LONGTEXT',
                'date_viewed' => 'DATETIME DEFAULT NULL',
                'date_processed' => 'DATETIME DEFAULT NULL',
                'processed_by_user_id' => 'INT UNSIGNED DEFAULT NULL',
                'processed' => 'TINYINT(1) DEFAULT \'0\'',
                'internal_comment' => 'TEXT DEFAULT NULL',
                'submission_referrer' => 'VARCHAR(255) DEFAULT NULL',
                'visitor_referrer' => 'VARCHAR(255) DEFAULT NULL',
                'visitor_landing' => 'VARCHAR(255) DEFAULT NULL',
                'visit_start_date' => 'DATETIME DEFAULT NULL',
                'form_fill_time' => 'INT DEFAULT NULL',
                'spam' => 'VARCHAR(25) DEFAULT NULL',
                'KEY `submission-form_id` (`form_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%submission_property}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%submission_category}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%submission_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INTEGER DEFAULT \'0\'',
                'KEY  `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            Notification::tableName(),
            [
                'id' => 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'user_id' => 'INTEGER UNSIGNED NOT NULL',
                'date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'type' => 'ENUM(\'default\', \'primary\', \'success\', \'info\', \'warning\', \'danger\') DEFAULT \'default\'',
                'label' => 'VARCHAR(255) NOT NULL',
                'message' => 'TEXT NOT NULL',
                'viewed' => 'TINYINT UNSIGNED DEFAULT \'0\'',
                'KEY `user_id` (`user_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            Review::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'date_submitted' => 'TIMESTAMP NULL DEFAULT NULL',
                'author_user_id' => 'INT UNSIGNED DEFAULT NULL',
                'author_name' => 'VARCHAR(255) DEFAULT NULL',
                'author_email' => 'VARCHAR(255) DEFAULT NULL',
                'author_phone' => 'VARCHAR(255) DEFAULT NULL',
                'text' => 'TEXT DEFAULT NULL',
                'rate' => 'TINYINT DEFAULT NULL',
                'status' => 'enum(\'NEW\',\'APPROVED\',\'NOT APPROVED\') DEFAULT \'NEW\'',
                'KEY `ix-review-object_id-object_model_id-status` (`object_id`, `object_model_id`, `status`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ErrorLog::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'url_id' => 'INT UNSIGNED NOT NULL',
                'http_code' => 'SMALLINT',
                'timestamp' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'info' => 'TEXT DEFAULT NULL',
                'server_vars' => 'TEXT DEFAULT NULL',
                'request_vars' => 'TEXT DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            ErrorUrl::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'url' => 'TEXT DEFAULT NULL',
                'immediate_notify_count' => 'INT UNSIGNED DEFAULT \'0\'',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%auth_rule}}',
            [
                'name' => 'VARCHAR(64) NOT NULL PRIMARY KEY',
                'data' => 'TEXT',
                'created_at' => 'INT DEFAULT NULL',
                'updated_at' => 'INT DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%auth_item}}',
            [
                'name' => 'VARCHAR(64) NOT NULL PRIMARY KEY',
                'type' => 'INT NOT NULL',
                'description' => 'TEXT',
                'rule_name' => 'VARCHAR(64)',
                'data' => 'TEXT',
                'created_at' => 'INT DEFAULT NULL',
                'updated_at' => 'INT DEFAULT NULL',
                'KEY `rule_name` (`rule_name`)',
                'KEY `type` (`type`)',
                'CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`)
                    REFERENCES {{%auth_rule}} (`name`) ON DELETE SET NULL ON UPDATE CASCADE'
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%auth_item_child}}',
            [
                'parent' => 'VARCHAR(64) NOT NULL',
                'child' => 'VARCHAR(64) NOT NULL',
                'PRIMARY KEY (`parent`, `child`)',
                'KEY `child` (`child`)',
                'CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`)
                    REFERENCES {{%auth_item}} (`name`) ON DELETE CASCADE ON UPDATE CASCADE',
                'CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`)
                    REFERENCES {{%auth_item}} (`name`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%auth_assignment}}',
            [
                'item_name' => 'VARCHAR(64) NOT NULL',
                'user_id' => 'VARCHAR(64) NOT NULL',
                'created_at' => 'INT DEFAULT NULL',
                'updated_at' => 'INT DEFAULT NULL',
                'rule_name' => 'VARCHAR(64) DEFAULT NULL',
                'data' => 'TEXT',
                'PRIMARY KEY (`item_name`, `user_id`)',
                'KEY `rule_name` (`rule_name`)',
                'CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`)
                    REFERENCES {{%auth_item}} (`name`) ON DELETE CASCADE ON UPDATE CASCADE',
                'CONSTRAINT `auth_assignment_ibfk_2` FOREIGN KEY (`rule_name`)
                    REFERENCES {{%auth_rule}} (`name`) ON DELETE SET NULL ON UPDATE CASCADE',
            ],
            $tableOptions
        );
        $this->createTable(
            User::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'username' => 'VARCHAR(255) NOT NULL',
                'auth_key' => 'varbinary(32) NOT NULL',
                'password_hash' => 'VARCHAR(255) NOT NULL',
                'password_reset_token' => 'varbinary(32)',
                'email' => 'VARCHAR(255) NOT NULL',
                'status' => 'TINYINT UNSIGNED DEFAULT 10',
                'create_time' => 'INT NOT NULL',
                'update_time' => 'INT NOT NULL',
                'first_name' => 'VARCHAR(255)',
                'last_name' => 'VARCHAR(255)',
                'UNIQUE KEY `uq-user-username` (`username`)',
            ],
            $tableOptions
        );
        $this->createTable(
            UserService::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'user_id' => 'INT UNSIGNED NOT NULL',
                'service_type' => 'VARCHAR(255) NOT NULL',
                'service_id' => 'VARCHAR(255) NOT NULL',
                'KEY `ix-user_service-user_id` (`user_id`)',
                'UNIQUE KEY `uq-user-service-service_type-service_id` (`service_type`, `service_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%user_property}}',
            [
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%user_eav}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INTEGER DEFAULT \'0\'',
                'KEY `object_model_id` (`object_model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%user_category}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'category_id' => 'INTEGER UNSIGNED NOT NULL',
                'object_model_id' => 'INTEGER UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        // Data
        $this->batchInsert(
            Object::tableName(),
            [
                'name',
                'object_class',
                'object_table_name',
                'column_properties_table_name',
                'eav_table_name',
                'categories_table_name',
                'link_slug_category',
                'link_slug_static_value',
                'object_slug_attribute',
            ],
            [
                [
                    'Page',
                    Page::className(),
                    Yii::$app->db->schema->getRawTableName(Page::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%page_property}}'),
                    Yii::$app->db->schema->getRawTableName('{{%page_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%page_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%page_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%page_static_value_category}}'),
                    'slug',
                ],
                [
                    'Category',
                    Category::className(),
                    Yii::$app->db->schema->getRawTableName(Category::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%category_properties}}'),
                    Yii::$app->db->schema->getRawTableName('{{%category_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%category_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%category_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%category_static_value_category}}'),
                    'slug',
                ],
            ]
        );
        $this->insert(
            Object::tableName(),
            [
                'name' => 'Product',
                'object_class' => Product::className(),
                'object_table_name' => Yii::$app->db->schema->getRawTableName(Product::tableName()),
                'column_properties_table_name' => Yii::$app->db->schema->getRawTableName('{{%product_properties}}'),
                'eav_table_name' => Yii::$app->db->schema->getRawTableName('{{%product_eav}}'),
                'categories_table_name' => Yii::$app->db->schema->getRawTableName('{{%product_category}}'),
                'link_slug_category' => Yii::$app->db->schema->getRawTableName('{{%product_category_full_slug}}'),
                'link_slug_static_value' => Yii::$app->db->schema->getRawTableName('{{%product_static_value_category}}'),
                'object_slug_attribute' => 'slug',
            ]
        );
        $lastInsertId = Yii::$app->db->lastInsertID;
        $this->batchInsert(
            Route::tableName(),
            ['route', 'url_template', 'object_id', 'name'],
            [
                [
                    'shop/product/list',
                    Json::encode(
                        [
                            [
                                "class" => "app\\properties\\url\\StaticPart",
                                "static_part" => "catalog",
                                "parameters" => [
                                    "category_group_id" => 1,
                                ],
                            ],
                            [
                                "class" => "app\\properties\\url\\PartialCategoryPathPart",
                                "category_group_id" => 1
                            ],
                        ]
                    ),
                    $lastInsertId,
                    ''
                ],
                [
                    'shop/product/show',
                    Json::encode(
                        [
                            [
                                "class" => "app\\properties\\url\\StaticPart",
                                "static_part" => "catalog",
                            ],
                            [
                                "class" => "app\\properties\\url\\FullCategoryPathPart",
                                "category_group_id" => 1,
                            ],
                            [
                                "class" => "app\\properties\\url\\ObjectSlugPart",
                            ],
                        ]
                    ),
                    $lastInsertId,
                    ''
                ],
            ]
        );
        $this->batchInsert(
            PropertyHandler::tableName(),
            [
                'name',
                'frontend_render_view',
                'frontend_edit_view',
                'backend_render_view',
                'backend_edit_view',
                'handler_class_name'
            ],
            [
                [
                    'Text',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    '\app\properties\handlers\text\TextProperty',
                ],
                [
                    'Select',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    '\app\properties\handlers\select\SelectProperty',
                ],
                [
                    'Checkbox',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    '\app\properties\handlers\checkbox\CheckboxProperty',
                ],
                [
                    'Text area',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    '\app\properties\handlers\textArea\TextAreaProperty',
                ],
            ]
        );
        $this->batchInsert(
            Layout::tableName(),
            [
                'name',
                'layout',
            ],
            [
                ['Default', '\\\\layouts\main'],
                ['Main page', '\\\\layouts\main-page'],
                ['Page without sidebar', '\\\\layouts\no-sidebar'],
            ]
        );
        $this->insert(
            View::tableName(),
            [
                'name' => 'Default',
                'view' => 'show',
                'category' => 'app',
                'internal_name' => 'default',
            ]
        );
        $this->insert(
            Page::tableName(),
            [
                'slug' => ':mainpage:',
                'content' => 'This is main page!',
                'parent_id' => 0,
                'title' => 'Main page',
            ]
        );
        $this->insert(
            CategoryGroup::tableName(),
            [
                'name' => 'Shop',
            ]
        );
        $this->insert(
            Category::tableName(),
            [
                'category_group_id' => Yii::$app->db->lastInsertID,
                'parent_id' => 0,
                'name' => 'Catalog',
                'title' => 'Catalog',
                'h1' => 'Catalog',
                'meta_description' => 'Catalog',
                'breadcrumbs_label' => 'Catalog',
                'slug' => 'catalog',
                'slug_compiled' => 'catalog',
            ]
        );
        $this->batchInsert(
            ShippingOption::tableName(),
            ['name', 'description', 'price_from', 'price_to', 'cost', 'sort', 'active'],
            [
                [
                    'Доставка курьером',
                    '<p>Наши курьеры быстро доставят товар в любую точку Москвы и Московской области!</p>',
                    '0',
                    '0',
                    '0',
                    '1',
                    '1',
                ],
                [
                    'Самовывоз',
                    '<p>Вы можете самостоятельно забрать заказ из нашего магазина.</p>',
                    '0',
                    '0',
                    '0',
                    '2',
                    '1',
                ],
                [
                    'Бесплатная доставка по Москве при заказе от 2500р',
                    '<p>Для Вас доставим бесплатно!</p>',
                    '0',
                    '0',
                    '0',
                    '3',
                    '1',
                ],
            ]
        );
        $this->batchInsert(
            PaymentType::tableName(),
            ['name', 'class', 'params', 'active', 'sort'],
            [
                [
                    'Наличные',
                    app\components\payment\CashPayment::className(),
                    '[]',
                    '1',
                    '1'
                ],
                [
                    'Робокасса (VISA, Webmoney, Яндекс.Деньги и др.)',
                    app\components\payment\RobokassaPayment::className(),
                    Json::encode(
                        [
                            'merchantLogin' => '',
                            'merchantPass1' => '',
                            'merchantPass2' => '',
                            'merchantUrl' => '',
                        ]
                    ),
                    '1',
                    '2'
                ],
                [
                    'PayU',
                    app\components\payment\PayUPayment::className(),
                    Json::encode(
                        [
                            'merchantName' => '',
                            'secretKey' => '',
                        ]
                    ),
                    '0',
                    '3'
                ],
                [
                    'RBK Money',
                    app\components\payment\RBKMoneyPayment::className(),
                    Json::encode(
                        [
                            'eshopId' => '',
                            'currency' => 'RUR', // RUR, USD, EUR, UAH
                            'language' => 'ru', // en, ru
                            'secretKey' => '',
                            'serviceName' => '',
                        ]
                    ),
                    '0',
                    '4'
                ],
                [
                    'IntellectMoney',
                    app\components\payment\IntellectMoneyPayment::className(),
                    Json::encode(
                        [
                            'eshopId' => '',
                            'currency' => 'RUR', // RUR, USD, EUR, UAH
                            'language' => 'ru', // en, ru
                            'secretKey' => '',
                            'serviceName' => '',
                        ]
                    ),
                    '0',
                    '5'
                ],
                [
                    'Interkassa',
                    app\components\payment\InterkassaPayment::className(),
                    Json::encode(
                        [
                            'checkoutId' => '',
                            'currency' => 'RUB', // RUB, USD, EUR, UAH, BYR, XAU, XTS
                            'locale' => 'ru',
                            'secretKey' => '',
                        ]
                    ),
                    '0',
                    '6'
                ],
                [
                    'Futubank',
                    app\components\payment\FutubankPayment::className(),
                    Json::encode(
                        [
                            'testing' => '1',
                            'merchant' => '',
                            'currency' => 'RUB',
                            'secretKey' => '',
                        ]
                    ),
                    '0',
                    '7'
                ],
                [
                    'Pay2Pay',
                    app\components\payment\Pay2PayPayment::className(),
                    Json::encode(
                        [
                            'hiddenKey' => '',
                            'currency' => 'RUB',
                            'language' => 'ru',
                            'merchantId' => '',
                            'secretKey' => '',
                            'testMode' => 0,
                        ]
                    ),
                    '0',
                    '8'
                ],
                [
                    'SpryPay',
                    app\components\payment\SpryPayPayment::className(),
                    Json::encode(
                        [
                            'currency' => 'rur',
                            'language' => 'ru',
                            'shopId' => '',
                            'secretKey' => '',
                        ]
                    ),
                    '0',
                    '9'
                ],
                [
                    'WalletOne',
                    app\components\payment\WalletOnePayment::className(),
                    Json::encode(
                        [
                            'currency' => 643,
                            'locale' => 'ru-RU',
                            'merchantId' => '',
                            'secretKey' => '',
                        ]
                    ),
                    '0',
                    '10'
                ],
                [
                    'PayOnline',
                    app\components\payment\PayOnlinePayment::className(),
                    Json::encode(
                        [
                            'currency' => 'RUB',
                            'language' => 'ru',
                            'merchantId' => '',
                            'privateKey' => '',
                        ]
                    ),
                    '0',
                    '11'
                ],
                [
                    'LiqPay',
                    app\components\payment\LiqPayPayment::className(),
                    Json::encode(
                        [
                            'currency' => 'RUB',
                            'language' => 'ru',
                            'privateKey' => '',
                            'publicKey' => '',
                        ]
                    ),
                    '0',
                    '12'
                ],
            ]
        );
        $this->insert(
            Object::tableName(),
            [
                'name' => 'Order',
                'object_class' => Order::className(),
                'object_table_name' => Yii::$app->db->schema->getRawTableName(Order::tableName()),
                'column_properties_table_name' => Yii::$app->db->schema->getRawTableName('{{%order_property}}'),
                'eav_table_name' => Yii::$app->db->schema->getRawTableName('{{%order_eav}}'),
                'categories_table_name' => Yii::$app->db->schema->getRawTableName('{{%order_category}}'),
                'link_slug_category' => Yii::$app->db->schema->getRawTableName('{{%order_category_full_slug}}'),
                'link_slug_static_value' => Yii::$app->db->schema->getRawTableName('{{%order_static_value_full_slug}}'),
                'object_slug_attribute' => 'slug',
            ]
        );
        $this->insert(
            PropertyGroup::tableName(),
            [
                'object_id' => Yii::$app->db->lastInsertID,
                'name' => 'Order form',
                'hidden_group_title' => 1,
            ]
        );
        $propertyGroupId = Yii::$app->db->lastInsertID;
        $this->batchInsert(
            Property::tableName(),
            ['property_group_id', 'name', 'key', 'property_handler_id', 'is_eav', 'handler_additional_params'],
            [
                [$propertyGroupId, 'Name', 'name', 1, 1, '{"rules":["required"]}'],
                [$propertyGroupId, 'Phone', 'phone', 1, 1, '{"rules":["required"]}'],
                [$propertyGroupId, 'E-mail', 'email', 1, 1, '{"rules":["required"]}'],
                [$propertyGroupId, 'Address', 'address', 1, 1, '{"rules":["required"]}'],
            ]
        );
        $this->batchInsert(
            Object::tableName(),
            [
                'name',
                'object_class',
                'object_table_name',
                'column_properties_table_name',
                'eav_table_name',
                'categories_table_name',
                'link_slug_category',
                'link_slug_static_value',
                'object_slug_attribute'
            ],
            [
                [
                    'Form',
                    \app\models\Form::className(),
                    Yii::$app->db->schema->getRawTableName(\app\models\Form::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%form_property}}'),
                    Yii::$app->db->schema->getRawTableName('{{%form_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%form_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%form_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%form_static_value_full_sluug}}'),
                    'slug'
                ],
                [
                    'Submission',
                    \app\models\Submission::className(),
                    Yii::$app->db->schema->getRawTableName(\app\models\Submission::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%submission_property}}'),
                    Yii::$app->db->schema->getRawTableName('{{%submission_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%submission_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%submission_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%submission_static_value_full_slug}}'),
                    'slug'
                ]
            ]
        );
        $this->insert(
            Object::tableName(),
            [
                'name' => 'User',
                'object_class' => User::className(),
                'object_table_name' => Yii::$app->db->schema->getRawTableName(User::tableName()),
                'column_properties_table_name' => Yii::$app->db->schema->getRawTableName('{{%user_property}}'),
                'eav_table_name' => Yii::$app->db->schema->getRawTableName('{{%user_eav}}'),
                'categories_table_name' => Yii::$app->db->schema->getRawTableName('{{%user_category}}'),
                'link_slug_category' => Yii::$app->db->schema->getRawTableName('{{%user_category_full_slug}}'),
                'link_slug_static_value' => Yii::$app->db->schema->getRawTableName('{{%user_static_value_full_slug}}'),
                'object_slug_attribute' => 'slug',
            ]
        );
        $this->insert(
            PropertyGroup::tableName(),
            [
                'object_id' => Yii::$app->db->lastInsertID,
                'name' => 'User form',
            ]
        );
        $this->insert(
            Navigation::tableName(),
            [
                'parent_id' => 0,
                'name' => 'Main menu',
                'route_params' => '{}',
            ]
        );
        $username = $email = $password = null;

        if (getenv("ADMIN_USERNAME")) {
            echo "INFO: Using admin user details provided by ENV variables...\n";
            $username = getenv("ADMIN_USERNAME");
            $email = getenv("ADMIN_EMAIL");
            $password = getenv("ADMIN_PASSWORD");

        } else {
            $stdIn = fopen("php://stdin", "r");
            do {
                echo 'Enter admin username (3 or more chars): ';
                $username = trim(fgets($stdIn));
            } while (mb_strlen($username) < 3);
            do {
                echo 'Enter admin email: ';
                $email = trim(fgets($stdIn));
            } while (preg_match('#^\w[\w\d\.\-_]*@[\w\d\.\-_]+\.\w{2,6}$#i', $email) != 1);
            do {
                do {
                    echo 'Enter admin password (8 or more chars): ';
                    $password = trim(fgets($stdIn));
                } while (mb_strlen($password) < 8);
                do {
                    echo 'Confirm admin password: ';
                    $confirmPassword = trim(fgets($stdIn));
                } while (mb_strlen($confirmPassword) < 8);
                if ($password != $confirmPassword) {
                    echo "Password does not match the confirm password\n";
                }
            } while ($password != $confirmPassword);
            fclose($stdIn);
        }

        $user = new User(['scenario' => 'signup']);
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->save(false);

        if (getenv("INSTALL_RUSSIAN_TRANSLATIONS")) {
            echo "INFO: Using translations details provided by ENV variables...\n";
            if (trim(strtolower(getenv("INSTALL_RUSSIAN_TRANSLATIONS"))) === 'y') {
                Yii::$app->language = 'ru-RU';
            }
        }
        else {
            $f = fopen( 'php://stdin', 'r' );
            echo "Install Russian translations? [y/N] ";
            while (true) {
                $answer = trim(fgets($f));

                if ($answer === 'y' || $answer === 'Y') {
                    Yii::$app->language = 'ru-RU';
                    break;
                } elseif ($answer === 'n' || $answer === 'N') {
                    break;
                }
                echo "Install Russian translations? [y/N]";
            }
            fclose($f);
        }
        $this->batchInsert(
            '{{%auth_item}}',
            ['name', 'type', 'description'],
            [
                ['admin', '1', Yii::t('app','Administrator')],
                ['manager', '1', Yii::t('app','Manager')],
                ['administrate', '2', Yii::t('app','Administrate panel')],
                ['api manage', '2', Yii::t('app','API management')],
                ['seo manage', '2', Yii::t('app','SEO management')],
                ['task manage', '2', Yii::t('app','Task management')],
                ['user manage', '2', Yii::t('app','User management')],
                ['cache manage', '2', Yii::t('app','Cache management')],
                ['content manage', '2', Yii::t('app','Content management')],
                ['shop manage', '2', Yii::t('app','Shop management')],
                ['order manage', '2', Yii::t('app','Order management')],
                ['category manage', '2', Yii::t('app','Category management')],
                ['product manage', '2', Yii::t('app','Product management')],
                ['property manage', '2', Yii::t('app','Property management')],
                ['view manage', '2', Yii::t('app','View management')],
                ['review manage', '2', Yii::t('app','Review management')],
                ['navigation manage', '2', Yii::t('app','Navigation management')],
                ['form manage', '2', Yii::t('app','Form management')],
                ['media manage', '2', Yii::t('app','Media management')],
                ['order status manage', '2', Yii::t('app','Order status management')],
                ['payment manage', '2', Yii::t('app','Payment type management')],
                ['shipping manage', '2', Yii::t('app','Shipping option management')],
                ['newsletter manage', '2', Yii::t('app','Newsletter management')],
                ['monitoring manage', '2', Yii::t('app','Monitoring management')],
                ['data manage', '2', Yii::t('app','Data management')],
                ['setting manage', '2', Yii::t('app','Setting management')],
            ]
        );
        $this->batchInsert(
            '{{%auth_item_child}}',
            ['parent', 'child'],
            [
                ['shop manage', 'category manage'],
                ['shop manage', 'product manage'],
                ['shop manage', 'order manage'],
                ['manager', 'administrate'],
                ['manager', 'content manage'],
                ['manager', 'order manage'],
                ['manager', 'shop manage'],
                ['manager', 'category manage'],
                ['manager', 'product manage'],
                ['manager', 'property manage'],
                ['manager', 'view manage'],
                ['manager', 'review manage'],
                ['manager', 'navigation manage'],
                ['manager', 'form manage'],
                ['manager', 'media manage'],
                ['admin', 'administrate'],
                ['admin', 'api manage'],
                ['admin', 'order manage'],
                ['admin', 'seo manage'],
                ['admin', 'task manage'],
                ['admin', 'user manage'],
                ['admin', 'cache manage'],
                ['admin', 'content manage'],
                ['admin', 'shop manage'],
                ['admin', 'category manage'],
                ['admin', 'product manage'],
                ['admin', 'property manage'],
                ['admin', 'view manage'],
                ['admin', 'review manage'],
                ['admin', 'navigation manage'],
                ['admin', 'form manage'],
                ['admin', 'media manage'],
                ['admin', 'order status manage'],
                ['admin', 'payment manage'],
                ['admin', 'shipping manage'],
                ['admin', 'monitoring manage'],
                ['admin', 'newsletter manage'],
                ['admin', 'data manage'],
                ['admin', 'setting manage'],
            ]
        );
        $this->insert(
            '{{%auth_assignment}}',
            [
                'item_name' => 'admin',
                'user_id' => $user->id,
            ]
        );
        // demo data
        $demo = null;
        if (getenv("INSTALL_DEMO_DATA")) {
            $demo = getenv("INSTALL_DEMO_DATA");
        } else {
            $stdIn = fopen("php://stdin", "r");
            do {
                echo 'Do you want to install demo data [y/n]: ';
                $demo = strtolower(trim(fgets($stdIn)));
            } while (!in_array($demo, ['y', 'n']));
            fclose($stdIn);
        }
        if ($demo == 'y') {
            echo "INFO: Installing demo data\n";
            $object = Object::getForClass(Product::className());
            $propertyGroup = new PropertyGroup;
            $propertyGroup->attributes = [
                'object_id' => $object->id,
                'name' => 'Тестовый набор свойств',
                'hidden_group_title' => 1,
            ];
            $propertyGroup->save();
            $propertyStaticValuesCount = [5, 3, 3, 2, 4, 5, 2];
            $propertyValues = [];
            for ($i = 1, $k = 1; $i <= 7; $i++) {
                $property = new Property;
                $property->attributes = [
                    'property_group_id' => $propertyGroup->id,
                    'name' => 'Свойство ' . $i,
                    'key' => 'svoystvo_' . $i,
                    'value_type' => 'STRING',
                    'property_handler_id' => 2,
                    'has_static_values' => 1,
                    'has_slugs_in_values' => 1,
                    'handler_additional_params' => '{}',
                ];
                $property->save(
                    true,
                    [
                        'property_group_id',
                        'name',
                        'key',
                        'value_type',
                        'property_handler_id',
                        'has_static_values',
                        'has_slugs_in_values',
                        'handler_additional_params',
                    ]
                );
                $propertyValues[$property->id] = [];
                for ($j = 1; $j <= $propertyStaticValuesCount[$i - 1]; $j++) {
                    $psv = new PropertyStaticValues;
                    $name = 'Значение ' . $k;
                    $psv->attributes = [
                        'property_id' => $property->id,
                        'name' => $name,
                        'value' => $name,
                        'slug' => Helper::createSlug($name),
                    ];
                    $psv->save(
                        true,
                        [
                          'property_id' ,
                          'name' ,
                          'value' ,
                          'slug' ,
                        ]
                    );
                    $propertyValues[$property->id][] = $psv->id;
                    $k++;
                }
            }
            $property = $psv = $propertyStaticValuesCount = null;
            $route = Route::findOne(['route' => 'shop/product/list']);
            $urlTemplate = Json::decode($route->url_template);
            foreach ($propertyValues as $propertyId => $values) {
                $urlTemplate[] = [
                    'class' => 'app\\properties\\url\\PropertyPart',
                    'property_id' => $propertyId,
                ];
            }
            $route->url_template = Json::encode($urlTemplate);
            $route->save(false, ['url_template']);
            $route = null;
            $categories = [
                'Фотоаппараты',
                'Телевизоры',
                'Мультиварки',
                'Музыкальные центры',
                'Холодильники',
                'Пылесосы',
                'Телефоны',
                'Планшеты',
                'Соковыжималки',
                'Электромясорубки',
                'Блендеры',
                'Аккустические системы',
                'Вентиляторы',
                'Кондиционеры',
            ];
            $category = \app\modules\shop\models\Category::findOne(['parent_id' => 0]);
            $category->attributes = [
                'name' => 'Каталог',
                'h1' => 'Каталог',
            ];
            $category->save();
            srand();
            $counter = 1;
            foreach ($categories as $categoryName) {
                $newCategory = new Category;
                $newCategory->attributes = [
                    'category_group_id' => 1,
                    'parent_id' => $category->id,
                    'name' => $categoryName,
                    'title' => 'Купить ' . mb_strtolower($categoryName, 'UTF-8') . ' в Москве и области',
                    'h1' => $categoryName,
                    'breadcrumbs_label' => $categoryName,
                    'slug' => Helper::createSlug($categoryName),
                    'announce' => '<p>Значимость этих проблем настолько очевидна, что рамки и место обучения кадров влечет за собой процесс внедрения и модернизации модели развития. Значимость этих проблем настолько очевидна, что укрепление и развитие структуры играет важную роль в формировании модели развития.</p>',
                    'content' => '<p>Таким образом рамки и место обучения кадров позволяет оценить значение системы обучения кадров, соответствует насущным потребностям. Повседневная практика показывает, что сложившаяся структура организации обеспечивает широкому кругу (специалистов) участие в формировании системы обучения кадров, соответствует насущным потребностям. Таким образом начало повседневной работы по формированию позиции влечет за собой процесс внедрения и модернизации систем массового участия. Идейные соображения высшего порядка, а также реализация намеченных плановых заданий позволяет выполнять важные задания по разработке существенных финансовых и административных условий.</p>

<p>Товарищи! сложившаяся структура организации представляет собой интересный эксперимент проверки направлений прогрессивного развития. Повседневная практика показывает, что постоянное информационно-пропагандистское обеспечение нашей деятельности играет важную роль в формировании систем массового участия. Разнообразный и богатый опыт постоянный количественный рост и сфера нашей активности в значительной степени обуславливает создание позиций, занимаемых участниками в отношении поставленных задач. Повседневная практика показывает, что укрепление и развитие структуры требуют от нас анализа системы обучения кадров, соответствует насущным потребностям. Товарищи! постоянный количественный рост и сфера нашей активности в значительной степени обуславливает создание систем массового участия.</p>

<p>Товарищи! укрепление и развитие структуры требуют от нас анализа системы обучения кадров, соответствует насущным потребностям. Повседневная практика показывает, что постоянное информационно-пропагандистское обеспечение нашей деятельности обеспечивает широкому кругу (специалистов) участие в формировании позиций, занимаемых участниками в отношении поставленных задач. Задача организации, в особенности же постоянное информационно-пропагандистское обеспечение нашей деятельности требуют определения и уточнения системы обучения кадров, соответствует насущным потребностям.</p>',
                ];
                $newCategory->save();
                for ($i = 1; $i <= 16; $i++) {
                    $name = 'Товар #' . $counter;
                    $product = new Product;
                    $product->attributes = [
                        'main_category_id' => $newCategory->id,
                        'name' => $name,
                        'title' => 'Купить ' . mb_strtolower($name, "UTF-8") . ' в Москве по превлекательной цене',
                        'slug' => Helper::createSlug($name),
                        'price' => rand(99, 999),
                        'announce' => '<p>Задача организации, в особенности же новая модель организационной деятельности в значительной степени обуславливает создание дальнейших направлений развития.</p>',
                        'content' => '<p>Повседневная практика показывает, что укрепление и развитие структуры способствует подготовки и реализации систем массового участия. Равным образом новая модель организационной деятельности обеспечивает широкому кругу (специалистов) участие в формировании модели развития. Таким образом постоянное информационно-пропагандистское обеспечение нашей деятельности представляет собой интересный эксперимент проверки систем массового участия.</p>

<p>Таким образом сложившаяся структура организации играет важную роль в формировании соответствующий условий активизации. Не следует, однако забывать, что консультация с широким активом способствует подготовки и реализации модели развития. С другой стороны дальнейшее развитие различных форм деятельности играет важную роль в формировании направлений прогрессивного развития.</p>

<p>Не следует, однако забывать, что сложившаяся структура организации способствует подготовки и реализации новых предложений. Не следует, однако забывать, что постоянное информационно-пропагандистское обеспечение нашей деятельности обеспечивает широкому кругу (специалистов) участие в формировании модели развития.</p>',
                    ];
                    $product->save(true, ['main_category_id', 'name', 'title', 'slug', 'price', 'announce', 'content']);
                    $images = [];
                    for ($j = 0; $j <= 6; $j++) {
                        if ($j == 0) {
                            $images[] = [
                                $object->id,
                                $product->id,
                                $name . '-' . $j . '.jpg',
                                '/demo/images/products/' . ($product->id % 13 + 1) . '.jpg',
                                '/demo/images/products/' . ($product->id % 13 + 1) . '.jpg',
                                'Изображение #' . $j . ' товара #' . $product->id,
                            ];
                        } else {
                            $images[] = [
                                $object->id,
                                $product->id,
                                $name . '-' . $j . '.jpg',
                                '/demo/images/products/large/' . (($product->id + $j) % 12 + 1) . '.jpg',
                                '/demo/images/products/large/' . (($product->id + $j) % 12 + 1) . '.jpg',
                                'Изображение #' . $j . ' товара #' . $product->id,
                            ];
                        }
                    }
                    $this->batchInsert(
                        Image::tableName(),
                        [
                            'object_id',
                            'object_model_id',
                            'filename',
                            'image_src',
                            'thumbnail_src',
                            'image_description',
                        ],
                        $images
                    );
                    $this->batchInsert(
                        '{{%product_category}}',
                        [
                            'category_id',
                            'object_model_id'
                        ],
                        [
                            [$category->id, $product->id],
                            [$newCategory->id, $product->id],
                        ]
                    );
                    $opg = new ObjectPropertyGroup;
                    $opg->attributes = [
                        'object_id' => $object->id,
                        'object_model_id' => $product->id,
                        'property_group_id' => $propertyGroup->id,
                    ];
                    $opg->save();
                    $rows = [];
                    foreach ($propertyValues as $propertyId => $values) {
                        $rows[] = [$object->id, $product->id, $values[rand(0, count($values) - 1)]];
                    }
                    $this->batchInsert(
                        ObjectStaticValues::tableName(),
                        ['object_id', 'object_model_id', 'property_static_value_id'],
                        $rows
                    );
                    $counter++;
                }
            }
            $page = Page::findOne(['parent_id' => 0]);
            $this->batchInsert(
                Page::tableName(),
                [
                    'slug',
                    'slug_compiled',
                    'content',
                    'show_type',
                    'breadcrumbs_label',
                    'title',
                    'h1',
                    'parent_id',
                ],
                [
                    [
                        'contacts',
                        'contacts',
                        '<p>Разнообразный и богатый опыт дальнейшее развитие различных форм деятельности влечет за собой процесс внедрения и модернизации системы обучения кадров, соответствует насущным потребностям. Разнообразный и богатый опыт постоянное информационно-пропагандистское обеспечение нашей деятельности представляет собой интересный эксперимент проверки системы обучения кадров, соответствует насущным потребностям. Равным образом реализация намеченных плановых заданий обеспечивает широкому кругу (специалистов) участие в формировании соответствующий условий активизации. Не следует, однако забывать, что рамки и место обучения кадров позволяет оценить значение дальнейших направлений развития. Идейные соображения высшего порядка, а также постоянный количественный рост и сфера нашей активности играет важную роль в формировании соответствующий условий активизации. Товарищи! реализация намеченных плановых заданий представляет собой интересный эксперимент проверки новых предложений.</p>
<p>Разнообразный и богатый опыт постоянное информационно-пропагандистское обеспечение нашей деятельности способствует подготовки и реализации направлений прогрессивного развития. Значимость этих проблем настолько очевидна, что постоянный количественный рост и сфера нашей активности влечет за собой процесс внедрения и модернизации системы обучения кадров, соответствует насущным потребностям. Значимость этих проблем настолько очевидна, что сложившаяся структура организации требуют от нас анализа дальнейших направлений развития. Повседневная практика показывает, что консультация с широким активом обеспечивает широкому кругу (специалистов) участие в формировании направлений прогрессивного развития.</p>
<p>Разнообразный и богатый опыт новая модель организационной деятельности играет важную роль в формировании дальнейших направлений развития. Идейные соображения высшего порядка, а также консультация с широким активом в значительной степени обуславливает создание позиций, занимаемых участниками в отношении поставленных задач. Разнообразный и богатый опыт постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение новых предложений. Идейные соображения высшего порядка, а также дальнейшее развитие различных форм деятельности позволяет оценить значение соответствующий условий активизации. Равным образом консультация с широким активом в значительной степени обуславливает создание систем массового участия.</p>',
                        'show',
                        'Контакты',
                        'Контакты',
                        'Контакты',
                        $page->id,
                    ],
                    [
                        'delivery',
                        'delivery',
                        '<p>Товарищи! дальнейшее развитие различных форм деятельности позволяет оценить значение существенных финансовых и административных условий. Товарищи! рамки и место обучения кадров способствует подготовки и реализации систем массового участия. Задача организации, в особенности же консультация с широким активом требуют определения и уточнения модели развития. Задача организации, в особенности же постоянное информационно-пропагандистское обеспечение нашей деятельности способствует подготовки и реализации системы обучения кадров, соответствует насущным потребностям. Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности обеспечивает широкому кругу (специалистов) участие в формировании форм развития.</p>
<p>Товарищи! постоянный количественный рост и сфера нашей активности требуют определения и уточнения модели развития. Значимость этих проблем настолько очевидна, что рамки и место обучения кадров позволяет выполнять важные задания по разработке форм развития.</p>
<p>С другой стороны постоянное информационно-пропагандистское обеспечение нашей деятельности играет важную роль в формировании форм развития. Значимость этих проблем настолько очевидна, что консультация с широким активом играет важную роль в формировании систем массового участия. Таким образом укрепление и развитие структуры играет важную роль в формировании существенных финансовых и административных условий. Задача организации, в особенности же постоянное информационно-пропагандистское обеспечение нашей деятельности в значительной степени обуславливает создание соответствующий условий активизации. Задача организации, в особенности же новая модель организационной деятельности позволяет оценить значение системы обучения кадров, соответствует насущным потребностям. Идейные соображения высшего порядка, а также новая модель организационной деятельности способствует подготовки и реализации модели развития.</p>',
                        'show',
                        'Доставка',
                        'Доставка',
                        'Доставка',
                        $page->id,
                    ],
                    [
                        'special-offer',
                        'special-offer',
                        '<p>Товарищи! реализация намеченных плановых заданий позволяет выполнять важные задания по разработке системы обучения кадров, соответствует насущным потребностям. Таким образом постоянный количественный рост и сфера нашей активности требуют от нас анализа дальнейших направлений развития.</p>
<p>Не следует, однако забывать, что дальнейшее развитие различных форм деятельности обеспечивает широкому кругу (специалистов) участие в формировании существенных финансовых и административных условий. Не следует, однако забывать, что постоянное информационно-пропагандистское обеспечение нашей деятельности влечет за собой процесс внедрения и модернизации новых предложений. Повседневная практика показывает, что рамки и место обучения кадров в значительной степени обуславливает создание системы обучения кадров, соответствует насущным потребностям.</p>
<p>Товарищи! укрепление и развитие структуры способствует подготовки и реализации дальнейших направлений развития. Таким образом постоянный количественный рост и сфера нашей активности способствует подготовки и реализации форм развития. Не следует, однако забывать, что новая модель организационной деятельности требуют определения и уточнения позиций, занимаемых участниками в отношении поставленных задач.</p>',
                        'show',
                        'Специальные предложения',
                        'Специальные предложения',
                        'Специальные предложения',
                        $page->id,
                    ],
                ]
            );
            $contactPageId = Yii::$app->db->lastInsertID;
            $this->insert(
                Page::tableName(),
                [
                    'slug' => 'news',
                    'slug_compiled' => 'news',
                    'show_type' => 'list',
                    'title' => 'Новости',
                    'breadcrumbs_label' => 'Новости',
                    'h1' => 'Новости',
                    'parent_id' => $page->id,
                ]
            );
            $newsPageId = Yii::$app->db->lastInsertID;
            $this->batchInsert(
                Page::tableName(),
                [
                    'slug',
                    'slug_compiled',
                    'content',
                    'announce',
                    'show_type',
                    'title',
                    'breadcrumbs_label',
                    'h1',
                    'parent_id',
                ],
                [
                    [
                        'first',
                        'news/first',
                        '<p>Разнообразный и богатый опыт начало повседневной работы по формированию позиции позволяет выполнять важные задания по разработке систем массового участия. Идейные соображения высшего порядка, а также консультация с широким активом влечет за собой процесс внедрения и модернизации существенных финансовых и административных условий. Таким образом постоянный количественный рост и сфера нашей активности в значительной степени обуславливает создание направлений прогрессивного развития. Товарищи! консультация с широким активом влечет за собой процесс внедрения и модернизации направлений прогрессивного развития. Разнообразный и богатый опыт реализация намеченных плановых заданий в значительной степени обуславливает создание позиций, занимаемых участниками в отношении поставленных задач.</p>
<p>Задача организации, в особенности же реализация намеченных плановых заданий влечет за собой процесс внедрения и модернизации дальнейших направлений развития. Разнообразный и богатый опыт постоянное информационно-пропагандистское обеспечение нашей деятельности влечет за собой процесс внедрения и модернизации систем массового участия. Не следует, однако забывать, что консультация с широким активом требуют от нас анализа дальнейших направлений развития. Идейные соображения высшего порядка, а также новая модель организационной деятельности требуют от нас анализа системы обучения кадров, соответствует насущным потребностям.</p>',
                        '<p>Равным образом постоянный количественный рост и сфера нашей активности играет важную роль в формировании систем массового участия. С другой стороны укрепление и развитие структуры позволяет выполнять важные задания по разработке позиций, занимаемых участниками в отношении поставленных задач.</p>',
                        'show',
                        'Первая новость',
                        'Первая новость',
                        'Первая новость',
                        $newsPageId,
                    ],
                    [
                        'second',
                        'news/second',
                        '<p>Не следует, однако забывать, что реализация намеченных плановых заданий позволяет выполнять важные задания по разработке новых предложений. Товарищи! укрепление и развитие структуры позволяет оценить значение системы обучения кадров, соответствует насущным потребностям. Задача организации, в особенности же консультация с широким активом способствует подготовки и реализации позиций, занимаемых участниками в отношении поставленных задач. Идейные соображения высшего порядка, а также сложившаяся структура организации требуют от нас анализа соответствующий условий активизации.</p>
<p>Разнообразный и богатый опыт укрепление и развитие структуры в значительной степени обуславливает создание форм развития. Повседневная практика показывает, что дальнейшее развитие различных форм деятельности позволяет выполнять важные задания по разработке дальнейших направлений развития. Повседневная практика показывает, что постоянное информационно-пропагандистское обеспечение нашей деятельности играет важную роль в формировании систем массового участия.
Не следует, однако забывать, что постоянный количественный рост и сфера нашей активности представляет собой интересный эксперимент проверки модели развития. Повседневная практика показывает, что дальнейшее развитие различных форм деятельности способствует подготовки и реализации модели развития.</p>',
                        '<p>Идейные соображения высшего порядка, а также рамки и место обучения кадров обеспечивает широкому кругу (специалистов) участие в формировании дальнейших направлений развития.</p>',
                        'show',
                        'Вторая новость',
                        'Вторая новость',
                        'Вторая новость',
                        $newsPageId,
                    ],
                    [
                        'third',
                        'news/third',
                        '<p>Таким образом начало повседневной работы по формированию позиции обеспечивает широкому кругу (специалистов) участие в формировании новых предложений. Повседневная практика показывает, что дальнейшее развитие различных форм деятельности играет важную роль в формировании соответствующий условий активизации. С другой стороны начало повседневной работы по формированию позиции влечет за собой процесс внедрения и модернизации новых предложений.</p>
<p>Задача организации, в особенности же новая модель организационной деятельности представляет собой интересный эксперимент проверки новых предложений. Таким образом рамки и место обучения кадров влечет за собой процесс внедрения и модернизации модели развития. Таким образом постоянное информационно-пропагандистское обеспечение нашей деятельности в значительной степени обуславливает создание соответствующий условий активизации. Равным образом укрепление и развитие структуры в значительной степени обуславливает создание систем массового участия.</p>
<p>Значимость этих проблем настолько очевидна, что новая модель организационной деятельности обеспечивает широкому кругу (специалистов) участие в формировании новых предложений. Значимость этих проблем настолько очевидна, что начало повседневной работы по формированию позиции играет важную роль в формировании дальнейших направлений развития.</p>',
                        '<p>С другой стороны новая модель организационной деятельности играет важную роль в формировании систем массового участия. Таким образом укрепление и развитие структуры влечет за собой процесс внедрения и модернизации форм развития.</p>',
                        'show',
                        'Третья новость',
                        'Третья новость',
                        'Третья новость',
                        $newsPageId,
                    ],
                    [
                        'fourth',
                        'news/fourth',
                        '<p>Товарищи! постоянное информационно-пропагандистское обеспечение нашей деятельности обеспечивает широкому кругу (специалистов) участие в формировании новых предложений. Идейные соображения высшего порядка, а также рамки и место обучения кадров позволяет оценить значение дальнейших направлений развития. С другой стороны дальнейшее развитие различных форм деятельности в значительной степени обуславливает создание соответствующий условий активизации. Разнообразный и богатый опыт начало повседневной работы по формированию позиции представляет собой интересный эксперимент проверки существенных финансовых и административных условий. Товарищи! консультация с широким активом способствует подготовки и реализации позиций, занимаемых участниками в отношении поставленных задач.</p>
<p>Разнообразный и богатый опыт сложившаяся структура организации позволяет оценить значение дальнейших направлений развития. Таким образом укрепление и развитие структуры обеспечивает широкому кругу (специалистов) участие в формировании существенных финансовых и административных условий.</p>',
                        '<p>Идейные соображения высшего порядка, а также дальнейшее развитие различных форм деятельности позволяет выполнять важные задания по разработке существенных финансовых и административных условий.</p>',
                        'show',
                        'Четвертая новость',
                        'Четвертая новость',
                        'Четвертая новость',
                        $newsPageId,
                    ],
                    [
                        'fifth',
                        'news/fifth',
                        '<p>Не следует, однако забывать, что новая модель организационной деятельности позволяет оценить значение направлений прогрессивного развития. Задача организации, в особенности же постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Равным образом начало повседневной работы по формированию позиции обеспечивает широкому кругу (специалистов) участие в формировании позиций, занимаемых участниками в отношении поставленных задач.</p>
<p>Таким образом начало повседневной работы по формированию позиции в значительной степени обуславливает создание форм развития. Значимость этих проблем настолько очевидна, что начало повседневной работы по формированию позиции требуют от нас анализа системы обучения кадров, соответствует насущным потребностям. Значимость этих проблем настолько очевидна, что постоянный количественный рост и сфера нашей активности представляет собой интересный эксперимент проверки систем массового участия. Идейные соображения высшего порядка, а также укрепление и развитие структуры способствует подготовки и реализации дальнейших направлений развития. Таким образом консультация с широким активом требуют определения и уточнения новых предложений. Равным образом консультация с широким активом в значительной степени обуславливает создание системы обучения кадров, соответствует насущным потребностям.</p>',
                        '<p>Повседневная практика показывает, что консультация с широким активом способствует подготовки и реализации модели развития.</p>',
                        'show',
                        'Пятая новость',
                        'Пятая новость',
                        'Пятая новость',
                        $newsPageId,
                    ],
                ]
            );
            $navigation = Navigation::findOne(['parent_id' => 0]);
            $this->batchInsert(
                Navigation::tableName(),
                [
                    'parent_id',
                    'name',
                    'url',
                    'route_params',
                ],
                [
                    [$navigation->id, 'Новости', '/news', '{}'],
                    [$navigation->id, 'Специальные предложения', '/special-offer', '{}'],
                    [$navigation->id, 'Доставка', '/delivery', '{}'],
                    [$navigation->id, 'Контакты', '/contacts', '{}'],
                ]
            );
            $object = Object::getForClass(Form::className());
            $this->insert(
                PropertyGroup::tableName(),
                [
                    'object_id' => $object->id,
                    'name' => 'Contact form',
                    'hidden_group_title' => 1,
                ]
            );
            $propertyGroupId = Yii::$app->db->lastInsertID;
            $this->batchInsert(
                Property::tableName(),
                ['property_group_id', 'name', 'key', 'property_handler_id', 'is_eav', 'handler_additional_params'],
                [
                    [$propertyGroupId, 'Имя', 'name', 1, 1, '{"rules":["required"]}'],
                    [$propertyGroupId, 'E-mail', 'email', 1, 1, '{"rules":["required"]}'],
                    [$propertyGroupId, 'Сообщение', 'message', 1, 1, '{"rules":["required"]}'],
                ]
            );
            $this->insert(
                Form::tableName(),
                [
                    'name' => 'Контактная форма',
                    'email_notification_addresses' => 'example@example.com'
                ]
            );
            $formId = Yii::$app->db->lastInsertID;
            $this->insert(
                ObjectPropertyGroup::tableName(),
                [
                    'object_id' => $object->id,
                    'object_model_id' => $formId,
                    'property_group_id' => $propertyGroupId,
                ]
            );
            $this->insert(
                View::tableName(),
                [
                    'name' => 'Contact page view',
                    'view' => '@app/views/default/contact',
                    'category' => 'app',
                    'internal_name' => 'contact',
                ]
            );
            $viewId = Yii::$app->db->lastInsertID;
            $object = Object::getForClass(Page::className());
            $this->insert(
                ViewObject::tableName(),
                [
                    'object_id' => $object->id,
                    'object_model_id' => $contactPageId,
                    'view_id' => $viewId,
                ]
            );
            $user = new User(['scenario' => 'signup']);
            $user->username = 'manager';
            $user->password = 'manager';
            $user->email = 'example@example.com';
            $user->save(false);
            $this->insert(
                '{{%auth_assignment}}',
                [
                    'item_name' => 'manager',
                    'user_id' => $user->id,
                ]
            );
        }
    }

    public function down()
    {
        $this->dropTable('{{%user_category}}');
        $this->dropTable('{{%user_eav}}');
        $this->dropTable('{{%user_property}}');
        $this->dropTable(UserService::tableName());
        $this->dropTable(User::tableName());
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');
        $this->dropTable(ErrorUrl::tableName());
        $this->dropTable(ErrorLog::tableName());
        $this->dropTable(Review::tableName());
        $this->dropTable(Notification::tableName());
        $this->dropTable('{{%submission_eav}}');
        $this->dropTable('{{%submission_category}}');
        $this->dropTable('{{%submission_property}}');
        $this->dropTable(Submission::tableName());
        $this->dropTable('{{%form_property}}');
        $this->dropTable('{{%form_eav}}');
        $this->dropTable(Form::tableName());
        $this->dropTable('{{%order_category}}');
        $this->dropTable('{{%order_eav}}');
        $this->dropTable('{{%order_property}}');
        $this->dropTable(PaymentType::tableName());
        $this->dropTable(ShippingOption::tableName());
        $this->dropTable(OrderChat::tableName());
        $this->dropTable(OrderTransaction::tableName());
        $this->dropTable(OrderItem::tableName());
        $this->dropTable(Order::tableName());
        $this->dropTable(SubscribeEmail::tableName());
        $this->dropTable(CategoryGroupRouteTemplates::tableName());
        $this->dropTable('{{%product_category}}');
        $this->dropTable('{{%property_category}}');
        $this->dropTable('{{%category_eav}}');
        $this->dropTable(Category::tableName());
        $this->dropTable(CategoryGroup::tableName());
        $this->dropTable('{{%page_eav}}');
        $this->dropTable('{{%page_category}}');
        $this->dropTable('{{%page_property}}');
        $this->dropTable(Page::tableName());
        $this->dropTable(ViewObject::tableName());
        $this->dropTable(View::tableName());
        $this->dropTable(Layout::tableName());
        $this->dropTable('{{%product_property}}');
        $this->dropTable(Product::tableName());
        $this->dropTable(Property::tableName());
        $this->dropTable(Route::tableName());
        $this->dropTable('{{%product_eav}}');
        $this->dropTable('{{%product_static_value_full_slug}}');
        $this->dropTable('{{%product_category_full_slug}}');
        $this->dropTable(PropertyStaticValues::tableName());
        $this->dropTable(PropertyHandler::tableName());
        $this->dropTable(PropertyGroup::tableName());
        $this->dropTable(ObjectStaticValues::tableName());
        $this->dropTable(ObjectPropertyGroup::tableName());
        $this->dropTable(Object::tableName());
        $this->dropTable(DynamicContent::tableName());
        $this->dropTable(Navigation::tableName());
        $this->dropTable(Image::tableName());
        $this->dropTable('{{%session}}');
        $this->dropTable(ApiService::tableName());
    }
}
