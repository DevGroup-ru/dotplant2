<?php

use app\backend\models\ApiService;
use app\backend\models\BackendMenu;
use app\backend\models\Notification;
use app\backend\models\OrderChat;
use app\backgroundtasks\models\NotifyMessage;
use app\backgroundtasks\models\Task;
use app\extensions\DefaultTheme\models\ThemeActiveWidgets;
use app\extensions\DefaultTheme\models\ThemeParts;
use app\extensions\DefaultTheme\models\ThemeVariation;
use app\extensions\DefaultTheme\models\ThemeWidgetApplying;
use app\extensions\DefaultTheme\models\ThemeWidgets;
use app\models\City;
use app\models\Country;
use app\models\DynamicContent;
use app\models\ErrorLog;
use app\models\ErrorUrl;
use app\models\Form;
use app\models\Layout;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\models\PrefilteredPages;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyHandler;
use app\models\PropertyStaticValues;
use app\models\Route;
use app\models\Slide;
use app\models\Slider;
use app\models\SliderHandler;
use app\models\SpamChecker;
use app\models\Submission;
use app\models\SubscribeEmail;
use app\models\View;
use app\models\ViewObject;
use app\modules\config\models\Configurable;
use app\modules\core\helpers\EventTriggeringHelper;
use app\modules\core\models\ContentBlock;
use app\modules\core\models\ContentDecorator;
use app\modules\core\models\EventHandlers;
use app\modules\core\models\Events;
use app\modules\core\models\Extensions;
use app\modules\core\models\ExtensionTypes;
use app\modules\data\models\CommercemlGuid;
use app\modules\image\models\ErrorImage;
use app\modules\image\models\Image;
use app\modules\image\models\Thumbnail;
use app\modules\image\models\ThumbnailSize;
use app\modules\image\models\ThumbnailWatermark;
use app\modules\image\models\Watermark;
use app\modules\page\models\Page;
use app\modules\review\models\RatingItem;
use app\modules\review\models\RatingValues;
use app\modules\review\models\Review;
use app\modules\shop\models\Category;
use app\modules\shop\models\CategoryDiscount;
use app\modules\shop\models\CategoryGroup;
use app\modules\shop\models\CategoryGroupRouteTemplates;
use app\modules\shop\models\Contragent;
use app\modules\shop\models\Currency;
use app\modules\shop\models\CurrencyRateProvider;
use app\modules\shop\models\Customer;
use app\modules\shop\models\DeliveryInformation;
use app\modules\shop\models\Discount;
use app\modules\shop\models\DiscountCode;
use app\modules\shop\models\DiscountType;
use app\modules\shop\models\FilterSets;
use app\modules\shop\models\Measure;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderCode;
use app\modules\shop\models\OrderDeliveryInformation;
use app\modules\shop\models\OrderDiscount;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\PaymentType;
use app\modules\shop\models\Product;
use app\modules\shop\models\ProductDiscount;
use app\modules\shop\models\ProductListingSort;
use app\modules\shop\models\RelatedProduct;
use app\modules\shop\models\ShippingOption;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\models\SpecialPriceObject;
use app\modules\shop\models\UserDiscount;
use app\modules\shop\models\Warehouse;
use app\modules\shop\models\WarehouseEmail;
use app\modules\shop\models\WarehouseOpeninghours;
use app\modules\shop\models\WarehousePhone;
use app\modules\shop\models\WarehouseProduct;
use app\modules\user\models\User;
use app\modules\user\models\UserService;
use app\widgets\navigation\models\Navigation;
use Imagine\Image\ManipulatorInterface;
use yii\db\Migration;
use yii\db\Query;
use yii\db\Schema;
use yii\helpers\Json;

class m150531_084444_new_init extends Migration
{
    private function bulkInsert($table, $data)
    {
        foreach ($data as $row) {
            $this->insert($table, $row);
        }
    }

    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
//        $lang = 'ru-RU';
//        Yii::$app->language = $lang;
//        // drop all
//        $tables = $this->db->createCommand("SHOW TABLES")->queryAll();
//        $this->db->createCommand("SET foreign_key_checks = 0")->execute();
//        foreach ($tables as $table) {
//            $this->dropTable($table['Tables_in_dotplant2_test']);
//        }
        // create tables
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
                'content_block_name' => 'VARCHAR(80) DEFAULT \'content\'',
                'content' => 'TEXT DEFAULT NULL',
                'title' => 'VARCHAR(255)',
                'h1' => 'VARCHAR(255) DEFAULT NULL',
                'meta_description' => 'VARCHAR(255) DEFAULT NULL',
                'apply_if_last_category_id' => 'INT UNSIGNED DEFAULT NULL',
                'apply_if_params' => 'TEXT DEFAULT NULL',
                'object_id' => 'INT UNSIGNED DEFAULT NULL',
                'KEY `object_route` (`object_id`,`route`(80))',
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
                'dont_filter' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
                'dont_filter' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
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
                'sku' => 'VARCHAR(70) NOT NULL DEFAULT \'\'',
                'unlimited_count' => 'TINYINT NOT NULL DEFAULT \'1\'',
                'currency_id' => 'INT UNSIGNED NOT NULL DEFAULT \'1\'',
                'measure_id' => 'INT UNSIGNED NOT NULL',
                'KEY `ix-product-active-slug` (`active`, `slug`)',
                'KEY `ix-product-parent_id` (`parent_id`)',
                'KEY `sku` (`sku`)',
                'KEY `parent_active` (`parent_id`,`active`)',
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
                'name' => 'VARCHAR(255) DEFAULT NULL',
                'title' => 'TEXT NOT NULL',
                'h1' => 'TEXT DEFAULT NULL',
                'meta_description' => 'TEXT DEFAULT NULL',
                'breadcrumbs_label' => 'TEXT DEFAULT NULL',
                'announce' => 'TEXT DEFAULT NULL',
                'sort_order' => 'INT DEFAULT \'0\'',
                'date_added' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'date_modified' => 'TIMESTAMP NULL',
                'subdomain' => 'VARCHAR(255) DEFAULT NULL',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
                'KEY `cat_omid` (`category_id`,`object_model_id`)',
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
                'update_date' => 'TIMESTAMP NULL',
                'end_date' => "TIMESTAMP NULL",
                'cart_forming_time' => 'INT DEFAULT \'0\'',
                'order_stage_id' => 'INT UNSIGNED NOT NULL',
                'payment_type_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'assigned_id' => 'INT UNSIGNED DEFAULT NULL',
                'tax_id' => 'INT UNSIGNED DEFAULT NULL',
                'external_id' => 'VARCHAR(38) DEFAULT NULL',
                'items_count' => 'FLOAT UNSIGNED DEFAULT NULL',
                'total_price' => 'FLOAT DEFAULT \'0\'',
                'total_payed' => 'FLOAT DEFAULT \'0\'',
                'hash' => 'CHAR(32) NOT NULL',
                'is_deleted' => 'TINYINT UNSIGNED DEFAULT 0',
                'temporary' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1',
                'show_price_changed_notification' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'customer_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
                'contragent_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
                'in_cart' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'KEY `ix-order-user_id` (`user_id`)',
                'KEY `ix-order-manager_id` (`manager_id`)',
                'UNIQUE KEY `uq-order-hash` (`hash`)',
            ],
            $tableOptions
        );
        // @todo Set all float fields as unsigned
        $this->createTable(
            OrderItem::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'parent_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
                'order_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'product_id' => 'INT UNSIGNED NOT NULL',
                'custom_name' => 'VARCHAR(255)',
                'quantity' => 'FLOAT NOT NULL DEFAULT \'1\'',
                'price_per_pcs' => 'FLOAT NOT NULL DEFAULT \'0\'',
                'total_price_without_discount' => 'FLOAT NOT NULL DEFAULT \'0\'',
                'lock_product_price' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'discount_amount' => 'FLOAT NOT NULL DEFAULT \'0\'',
                'total_price' => 'FLOAT NOT NULL DEFAULT \'0\'',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
                'spam' => 'TINYINT(1) UNSIGNED DEFAULT 0',
                'is_deleted' => 'TINYINT UNSIGNED DEFAULT \'0\'',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
                'author_email' => 'VARCHAR(255) DEFAULT NULL',
                'review_text' => 'TEXT DEFAULT NULL',
                'status' => 'enum(\'NEW\',\'APPROVED\',\'NOT APPROVED\') DEFAULT \'NEW\'',
                'rating_id' => 'CHAR(32) CHARACTER SET utf8 DEFAULT NULL',
                'submission_id' => 'int unsigned NOT NULL',
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
                'username_is_temporary' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
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
                'property_group_id' => 'INT UNSIGNED NOT NULL',
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
        $this->createTable(
            BackendMenu::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'parent_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'name' => 'VARCHAR(255) NOT NULL',
                'route' => 'VARCHAR(255) NOT NULL',
                'icon' => 'VARCHAR(255) DEFAULT NULL',
                'sort_order' => 'INT UNSIGNED DEFAULT \'0\'',
                'added_by_ext' => 'VARCHAR(255) DEFAULT NULL',
                'rbac_check' => 'VARCHAR(64) DEFAULT NULL',
                'css_class' => 'VARCHAR(255) DEFAULT NULL',
                'translation_category' => 'VARCHAR(120) NOT NULL DEFAULT \'app\'',
                'KEY `ix-backendmenu-parent_id` (`parent_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%category_property}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%data_import}}',
            [
                'user_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'object_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'filename' => Schema::TYPE_STRING . ' DEFAULT NULL',
                'status' => 'enum(\'complete\',\'failed\',\'process\') NOT NULL DEFAULT \'process\'',
                'update_time' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'PRIMARY KEY (`user_id`,`object_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            PrefilteredPages::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'slug' => Schema::TYPE_STRING,
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'last_category_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
                'params' => 'TEXT',
                'title' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'announce' =>  'TEXT',
                'content' =>  'TEXT',
                'h1' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'meta_description' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'breadcrumbs_label' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'view_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $this->createTable(
            Task::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'action' => 'VARCHAR(255) NOT NULL',
                'type' => " enum('EVENT','REPEAT') NOT NULL DEFAULT 'EVENT'",
                'initiator' => 'INT UNSIGNED UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'description' => 'TEXT DEFAULT NULL',
                'params' => 'TEXT DEFAULT NULL',
                'init_event' => 'VARCHAR(255) DEFAULT NULL',
                'cron_expression' => 'VARCHAR(255) DEFAULT NULL',
                'status' => " enum('ACTIVE','STOPPED','RUNNING','FAILED','COMPLETED', 'PROCESS') NOT NULL DEFAULT 'ACTIVE'",
                'ts' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'fail_counter' => 'TINYINT UNSIGNED NOT NULL DEFAULT\'0\'',
                'options' => 'TEXT',
                'KEY `name` (`name`)',
            ],
            $tableOptions
        );
        $this->createTable(
            NotifyMessage::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'task_id' => 'INT UNSIGNED NOT NULL',
                'result_status' => 'enum(\'SUCCESS\',\'FAULT\') NOT NULL DEFAULT \'SUCCESS\'',
                'result' => 'TEXT DEFAULT NULL',
                'ts' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%data_export}}',
            [
                'user_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'object_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'filename' => Schema::TYPE_STRING . ' DEFAULT NULL',
                'status' => 'enum(\'complete\',\'failed\',\'process\') NOT NULL DEFAULT \'process\'',
                'update_time' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'PRIMARY KEY (`user_id`,`object_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ProductListingSort::tableName(),
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
        $this->createTable(
            SliderHandler::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'slider_widget' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'slider_edit_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'edit_model' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
            ],
            $tableOptions
        );
        $this->createTable(
            Slider::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'slider_handler_id' => 'INT UNSIGNED DEFAULT 0',
                'image_width' => 'INT UNSIGNED DEFAULT 0',
                'image_height' => 'INT UNSIGNED DEFAULT 0',
                'resize_big_images' => 'TINYINT(1) NOT NULL DEFAULT 1',
                'resize_small_images' => 'TINYINT(1) NOT NULL DEFAULT 0',
                'css_class' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'params' => 'LONGTEXT NULL',
                'custom_slider_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'custom_slide_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
            ],
            $tableOptions
        );
        $this->createTable(
            Slide::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'slider_id' => 'INT UNSIGNED DEFAULT 0',
                'sort_order' => 'INT UNSIGNED DEFAULT 0',
                'image' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'link' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'custom_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'css_class' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'active' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1',
            ],
            $tableOptions
        );
        $this->createTable(
            RelatedProduct::tableName(),
            [
                'product_id' => 'INT UNSIGNED NOT NULL',
                'related_product_id' => 'INT UNSIGNED NOT NULL',
                'PRIMARY KEY (`product_id`, `related_product_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%dynagrid}}',
            [
                'id' => Schema::TYPE_STRING . ' DEFAULT \'\' PRIMARY KEY',
                'filter_id' => Schema::TYPE_STRING,
                'sort_id' => Schema::TYPE_STRING,
                'data' => Schema::TYPE_TEXT,
            ]
        );
        $this->createTable(
            '{{%dynagrid_dtl}}',
            [
                'id' => Schema::TYPE_STRING . ' DEFAULT \'\' PRIMARY KEY',
                'category' => Schema::TYPE_STRING,
                'name' => Schema::TYPE_STRING,
                'data' => Schema::TYPE_TEXT,
                'dynagrid_id' => Schema::TYPE_STRING,
                'UNIQUE `uniq_dtl` (`name`, `category`, `dynagrid_id`)',
            ]
        );
        $this->createTable(
            CurrencyRateProvider::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'class_name' => Schema::TYPE_STRING,
                'params' => Schema::TYPE_TEXT,
            ],
            $tableOptions
        );
        $this->createTable(
            Currency::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'iso_code' => Schema::TYPE_STRING,
                'is_main' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'convert_nominal' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 1',
                'convert_rate' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 1',
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'intl_formatting' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'min_fraction_digits' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'max_fraction_digits' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 2',
                'dec_point' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'.\'',
                'thousands_sep' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \' \'',
                'format_string' => Schema::TYPE_STRING,
                'additional_rate' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'additional_nominal' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'currency_rate_provider_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        //
        $this->createTable(
            Warehouse::tableName(),
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
                'KEY `wh_country` (`country_id`)',
                'KEY `wh_city` (`city_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            WarehousePhone::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'phone' => Schema::TYPE_STRING . ' NOT NULL',
                'name' => Schema::TYPE_STRING,
                'KEY `wh_phone` (`warehouse_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            WarehouseEmail::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'email' => Schema::TYPE_STRING . ' NOT NULL',
                'name' => Schema::TYPE_STRING,
                'KEY `wh_email` (`warehouse_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            WarehouseOpeninghours::tableName(),
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
                'KEY `wh_hours` (`warehouse_id`)',

            ],
            $tableOptions
        );
        $this->createTable(
            Country::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'iso_code' => Schema::TYPE_STRING, // ISO 3166-1 alpha-3
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'slug' => Schema::TYPE_STRING,
            ],
            $tableOptions
        );
        $this->createTable(
            City::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'slug' => Schema::TYPE_STRING,
                'country_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'KEY `city_country` (`country_id`)'
            ],
            $tableOptions
        );
        $this->createTable(
            WarehouseProduct::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'warehouse_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'product_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'in_warehouse' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'reserved_count' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'sku' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'UNIQUE `wh_pr` (`warehouse_id`, `product_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            RatingItem::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'rating_group' => Schema::TYPE_STRING . ' NOT NULL',
                'min_value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'max_value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 5',
                'step_value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1',
                'require_review' => Schema::TYPE_BOOLEAN . ' DEFAULT 0',
                'allow_guest' => 'TINYINT(1) DEFAULT \'0\'',
                'KEY `ix-rating_item-rating_group` (`rating_group`)',
            ]
        );
        $this->createTable(
            RatingValues::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'rating_id' => 'CHAR(32) CHARACTER SET utf8 NOT NULL',
                'object_id' => 'INT UNSIGNED NOT NULL',
                'object_model_id' => 'INT UNSIGNED NOT NULL',
                'rating_item_id' => 'INT UNSIGNED NOT NULL',
                'value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'user_id' => 'INT UNSIGNED NOT NULL',
                'date' => Schema::TYPE_DATETIME . ' NOT NULL',
                'KEY `ix-rating_values-rating_id` (`rating_id`)',
                'KEY `ix-rating_values-object_id-object_model_id` (`object_id`,`object_model_id`)',
            ]
        );
        $this->createTable(
            SpamChecker::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'behavior' => 'VARCHAR(255) NOT NULL',
                'api_key' => 'VARCHAR(90) DEFAULT NULL',
                'name' => 'VARCHAR(50) DEFAULT NULL',
                'author_field' => 'VARCHAR(50) DEFAULT NULL',
                'content_field' => 'VARCHAR(50) DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            Configurable::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'module' => Schema::TYPE_STRING . ' NOT NULL',
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'section_name' => Schema::TYPE_STRING . ' NOT NULL',
                'display_in_config' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
            ],
            $tableOptions
        );
        $this->createTable(
            Thumbnail::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'img_id' => 'INT UNSIGNED NOT NULL',
                'thumb_path' => 'VARCHAR(255) NOT NULL',
                'size_id' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            ThumbnailSize::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'width' => 'INT UNSIGNED NOT NULL',
                'height' => 'INT UNSIGNED NOT NULL',
                'default_watermark_id' => 'INT UNSIGNED NULL',
                'resize_mode' => 'ENUM(\'' . ManipulatorInterface::THUMBNAIL_INSET . '\',\'' . ManipulatorInterface::THUMBNAIL_OUTBOUND . '\') DEFAULT \'' . ManipulatorInterface::THUMBNAIL_INSET . '\'',
            ],
            $tableOptions
        );
        $this->createTable(
            Watermark::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'watermark_path' => 'VARCHAR(255) NOT NULL',
                'position' => 'enum(\'TOP LEFT\',\'TOP RIGHT\',\'BOTTOM LEFT\',\'BOTTOM RIGHT\',\'CENTER\') NOT NULL DEFAULT \'TOP LEFT\''
            ],
            $tableOptions
        );
        $this->createTable(
            ThumbnailWatermark::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'thumb_id' => 'INT UNSIGNED NOT NULL',
                'water_id' => 'INT UNSIGNED NOT NULL',
                'compiled_src' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            ErrorImage::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'img_id' => 'INT UNSIGNED NOT NULL',
                'class_name' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            ContentBlock::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'key' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'value' => Schema::TYPE_TEXT,
                'preload' => 'TINYINT DEFAULT \'0\'',
            ]
        );
        $this->createTable(
            ContentDecorator::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'added_by_ext' => Schema::TYPE_STRING . ' NOT NULL',
                'post_decorator' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'class_name' => Schema::TYPE_STRING . ' NOT NULL',
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $this->createTable(
            Events::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'owner_class_name' => Schema::TYPE_STRING . ' NOT NULL',
                'event_name' => Schema::TYPE_STRING . ' NOT NULL',
                'event_class_name' => Schema::TYPE_STRING . ' NOT NULL',
                'selector_prefix' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'event_description' => Schema::TYPE_TEXT . ' NOT NULL',
                'documentation_link' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'UNIQUE `event_class_name` (`event_class_name`(50))',
            ],
            $tableOptions
        );
        $this->createTable(
            EventHandlers::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'event_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'handler_class_name' => Schema::TYPE_STRING . ' NOT NULL',
                'handler_function_name' => Schema::TYPE_STRING . ' NOT NULL',
                'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'non_deletable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'triggering_type' => Schema::TYPE_STRING . ' NOT NULL',
                'KEY `by_event_active` (`event_id`, `is_active`)',
            ],
            $tableOptions
        );
        $this->createTable(
            Extensions::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'force_version' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'dev-master\'',
                'type' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'latest_version' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'current_package_version_timestamp' => Schema::TYPE_TIMESTAMP . ' NULL',
                'latest_package_version_timestamp' => Schema::TYPE_TIMESTAMP . ' NULL',
                'homepage' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'namespace_prefix' => Schema::TYPE_STRING . ' NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            ExtensionTypes::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            CommercemlGuid::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'guid' => Schema::TYPE_STRING,
                'name' => Schema::TYPE_TEXT,
                'model_id' => Schema::TYPE_BIGINT,
                'type' => 'ENUM(\'PRODUCT\', \'CATEGORY\', \'PROPERTY\') DEFAULT \'PRODUCT\'',
            ],
            $tableOptions
        );
        $this->createTable(
            OrderStage::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'name_frontend' => 'VARCHAR(255) DEFAULT NULL',
                'name_short' => 'VARCHAR(255) DEFAULT NULL',
                'is_initial' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'is_buyer_stage' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'become_non_temporary' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'is_in_cart' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'immutable_by_user' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1',
                'immutable_by_manager' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'immutable_by_assigned' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'reach_goal_ym' => 'VARCHAR(255) DEFAULT NULL',
                'reach_goal_ga' => 'VARCHAR(255) DEFAULT NULL',
                'event_name' => 'VARCHAR(255) DEFAULT NULL',
                'view' => 'VARCHAR(255) DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            OrderStageLeaf::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'stage_from_id' => 'INT UNSIGNED NOT NULL',
                'stage_to_id' => 'INT UNSIGNED NOT NULL',
                'sort_order' => 'INT NOT NULL DEFAULT 0',
                'button_label' => 'VARCHAR(255) NOT NULL',
                'button_css_class' => 'VARCHAR(255) DEFAULT NULL',
                'notify_buyer' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'buyer_notification_view' => 'VARCHAR(255) DEFAULT NULL',
                'notify_manager' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1',
                'manager_notification_view' => 'VARCHAR(255) DEFAULT NULL',
                'assign_to_user_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
                'assign_to_role' => 'VARCHAR(255) DEFAULT NULL',
                'notify_new_assigned_user' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1',
                'role_assignment_policy' => "ENUM('random','fair_distribution','last_picked_from_role')",
                'event_name' => 'VARCHAR(255) DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            Discount::tableName(),
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
            DiscountCode::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'code' => Schema::TYPE_STRING . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'valid_from' => 'TIMESTAMP NULL DEFAULT NULL',
                'valid_till' => 'TIMESTAMP NULL DEFAULT NULL',
                'maximum_uses' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
            ]
        );
        $this->createTable(
            CategoryDiscount::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ]
        );
        $this->createTable(
            UserDiscount::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ]
        );
        $this->createTable(
            OrderDiscount::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'order_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'applied_date' => Schema::TYPE_TIMESTAMP
            ]
        );
        $this->createTable(
            ProductDiscount::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'product_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ]
        );
        $this->createTable(
            DiscountType::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'class' => Schema::TYPE_STRING . ' NOT NULL',
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'checking_class' => "ENUM('Order','OrderItem') NOT NULL",
                'sort_order' => Schema::TYPE_INTEGER .' NOT NULL DEFAULT 0',
                'add_view' => 'VARCHAR(255) DEFAULT NULL',
            ]
        );
        $this->createTable(
            SpecialPriceList::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'object_id' => Schema::TYPE_SMALLINT .' NOT NULL',
                'class' => Schema::TYPE_STRING . ' NOT NULL',
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'sort_order' => Schema::TYPE_INTEGER .' NOT NULL DEFAULT 0',
                'params' => Schema::TYPE_TEXT,
                'type' => "ENUM('core', 'discount', 'delivery', 'tax' ,'project') DEFAULT 'project'",
                'handler' => 'VARCHAR(255) DEFAULT NULL',
            ]
        );
        $this->createTable(
            FilterSets::tableName(),
            [
            'id' => Schema::TYPE_PK,
                'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'property_id' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'is_filter_by_price' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'delegate_to_children' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
            ]
        );
        //
        $this->createTable(
            Customer::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT 0',
                'first_name' => Schema::TYPE_STRING,
                'middle_name' => Schema::TYPE_STRING,
                'last_name' => Schema::TYPE_STRING,
                'email' => Schema::TYPE_STRING,
                'phone' => Schema::TYPE_STRING,
            ],
            $tableOptions
        );
        $this->createTable(
            Contragent::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'customer_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'type' => "ENUM('Individual', 'Self-employed', 'Legal entity') NOT NULL DEFAULT 'Individual'",
            ],
            $tableOptions
        );
        $this->createTable(
            DeliveryInformation::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'contragent_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'country_id' => Schema::TYPE_INTEGER . ' DEFAULT 0',
                'city_id' => Schema::TYPE_INTEGER . ' DEFAULT 0',
                'zip_code' => Schema::TYPE_STRING,
                'address' => Schema::TYPE_TEXT,
            ],
            $tableOptions
        );
        $this->createTable(
            OrderDeliveryInformation::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'order_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'shipping_option_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'shipping_price' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'shipping_price_total' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'planned_delivery_date' => Schema::TYPE_DATE,
                'planned_delivery_time' => Schema::TYPE_TIME,
                'planned_delivery_time_range' => Schema::TYPE_STRING,
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%customer_eav}}',
            [
                'id' => Schema::TYPE_PK,
                'object_model_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'property_group_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'key' => Schema::TYPE_STRING . ' NOT NULL',
                'value' => Schema::TYPE_TEXT,
                'sort_order' => Schema::TYPE_INTEGER,
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%contragent_eav}}',
            [
                'id' => Schema::TYPE_PK,
                'object_model_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'property_group_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'key' => 'VARCHAR(255) NOT NULL',
                'value' => Schema::TYPE_TEXT,
                'sort_order' => Schema::TYPE_INTEGER,
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%order_delivery_information_eav}}',
            [
                'id' => Schema::TYPE_PK,
                'object_model_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'property_group_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'key' => Schema::TYPE_STRING . ' NOT NULL',
                'value' => Schema::TYPE_TEXT,
                'sort_order' => Schema::TYPE_INTEGER,
            ],
            $tableOptions
        );
        $this->createTable(
            Measure::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'symbol' => 'VARCHAR(255) NOT NULL',
                'nominal' => 'FLOAT NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            SpecialPriceObject::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'special_price_list_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'object_model_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'price' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'name' => 'VARCHAR(255) DEFAULT NULL',
            ]
        );
        $this->createTable(
            ThemeParts::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'key' => Schema::TYPE_STRING,
                'global_visibility' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'multiple_widgets' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'is_cacheable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'cache_lifetime' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'cache_tags' => Schema::TYPE_TEXT . ' NULL',
                'cache_vary_by_session' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $this->createTable(
            ThemeWidgets::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'widget' => Schema::TYPE_STRING,
                'preview_image' => Schema::TYPE_STRING . ' NULL',

                'configuration_model' => Schema::TYPE_STRING . ' NULL',
                'configuration_view' => Schema::TYPE_STRING . ' NULL',
                'configuration_json' => Schema::TYPE_TEXT . ' NULL',

                'is_cacheable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'cache_lifetime' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'cache_tags' => Schema::TYPE_TEXT . ' NULL',
                'cache_vary_by_session' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $this->createTable(
            ThemeVariation::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NULL',
                'by_url' => Schema::TYPE_STRING . ' NULL',
                'by_route' => Schema::TYPE_STRING . ' NULL',
                'matcher_class_name' => Schema::TYPE_STRING . ' NULL',
                'exclusive' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $this->createTable(
            ThemeWidgetApplying::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'widget_id' => Schema::TYPE_INTEGER,
                'part_id' => Schema::TYPE_INTEGER,
                'UNIQUE `widget_part` (`widget_id`, `part_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            ThemeActiveWidgets::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'part_id' => Schema::TYPE_INTEGER,
                'widget_id' => Schema::TYPE_INTEGER,
                'variation_id' => Schema::TYPE_INTEGER,
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'configuration_json' => Schema::TYPE_TEXT,
                'KEY `variation` (`variation_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Config::tableName(),
            [
                'key' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'value' => 'TEXT NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Counter::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) DEFAULT NULL',
                'description' => 'TEXT DEFAULT NULL',
                'code' => 'TEXT NOT NULL ',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\LinkAnchor::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'model_name' => 'VARCHAR(60) NOT NULL',
                'model_id' => 'INT UNSIGNED NOT NULL',
                'anchor' => 'TEXT NOT NULL',
                'sort_order' =>  'INT DEFAULT \'0\'',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\LinkAnchorBinding::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'link_anchor_id' => 'INT UNSIGNED NOT NULL',
                'view_file' => 'VARCHAR(255) NOT NULL',
                'params_hash' => 'VARCHAR(255) DEFAULT NULL',
                'model_name' => 'VARCHAR(255) NOT NULL',
                'model_id' => 'VARCHAR(255) NOT NULL',
                'KEY `ix-link_anchor_id` (`link_anchor_id`)',
                'KEY `model_name` (`model_name`, `model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\ModelAnchorIndex::tableName(),
            [
                'model_name' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'model_id' => 'VARCHAR(255) NOT NULL',
                'next_index' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Redirect::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'type' => 'enum(\'STATIC\',\'PREG\') NOT NULL DEFAULT \'STATIC\'',
                'from' => 'VARCHAR(255) NOT NULL',
                'to' => 'VARCHAR(255) NOT NULL',
                'active' => 'TINYINT NOT NULL DEFAULT \'1\'',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Meta::tableName(),
            [
                'key' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'name' => 'VARCHAR(255) NOT NULL',
                'content' => 'VARCHAR(255) NOT NULL ',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Sitemap::tableName(),
            [
                'uid' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'url' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            OrderCode::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'order_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_code_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'status' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0'
            ]
        );
        // Data
        // Backend menu
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => 0,
                'name' => 'Root',
                'route' => '/backend/',
                'added_by_ext' => 'core',
            ]
        );
        $rootId = $this->db->lastInsertID;
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'icon', 'added_by_ext', 'rbac_check'],
            [
                [$rootId, 'Dashboard', 'backend/dashboard/index', 'dashboard', 'core', 'administrate'],
                [$rootId, 'Pages', 'page/backend/index', 'file-o', 'core', 'content manage'],
            ]
        );
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => $rootId,
                'name' => 'Shop',
                'icon' => 'shopping-cart',
                'added_by_ext' => 'core',
                'rbac_check' => 'shop manage',
                'route' => '',
            ]
        );
        $lastId = $this->db->lastInsertID;
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'icon', 'added_by_ext', 'rbac_check'],
            [
                [$lastId, 'Categories', 'shop/backend-category/index', 'tree', 'core', 'category manage'],
                [$lastId, 'Products', 'shop/backend-product/index', 'list', 'core', 'product manage'],
                [$lastId, 'Orders', 'shop/backend-order/index', 'list-alt', 'core', 'order manage'],
                [$lastId, 'Stages', 'shop/backend-stage/index', 'sitemap', 'core', 'order status manage'],
                [$lastId, 'Payment types', 'shop/backend-payment-type/index', 'info-circle', 'core', 'payment manage'],
                [$lastId, 'Filter sets', 'shop/backend-filter-sets/index', 'filter', 'core', 'category manage'],
                [$lastId, 'Shipping options', 'shop/backend-shipping-option/index', 'truck', 'core', 'shipping manage'],
                [$lastId, 'Categories groups', 'shop/backend-category-group/index', 'folder-o', 'core', 'category manage'],
                [$lastId, 'Prefiltered pages', 'shop/backend-prefiltered-pages/index', 'tag', 'core', 'shop manage'],
                [$lastId, 'Currencies', 'shop/backend-currencies/index', 'usd', 'core', 'shop manage'],
                [$lastId, 'Measures', 'shop/backend-measure/index', 'calculator', 'core', 'shop manage'],
                [$lastId, 'Discounts', 'shop/backend-discount/index', 'shekel', 'core', 'shop manage'],
                [$lastId, 'Warehouse', 'shop/backend-warehouse/index', 'cubes', 'core', 'shop manage'],
            ]
        );
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => $rootId,
                'name' => 'Properties',
                'icon' => 'cogs',
                'added_by_ext' => 'core',
                'rbac_check' => 'property manage',
                'route' => '',
            ]
        );
        $lastId = $this->db->lastInsertID;
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'icon', 'added_by_ext', 'rbac_check'],
            [
                [$lastId, 'Properties', 'backend/properties/index', 'cogs', 'core', 'property manage'],
                [$lastId, 'Views', 'backend/view/index', 'desktop', 'core', 'view manage'],
            ]
        );
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'icon', 'added_by_ext', 'rbac_check'],
            [
                [$rootId, 'Reviews', 'review/backend-review/index', 'comment', 'core', 'review manage'],
                [$rootId, 'Rating groups', 'review/backend-rating/index', 'star-half-o', 'core', 'review manage'],
                [$rootId, 'Navigation', 'backend/navigation/index', 'navicon', 'core', 'navigation manage'],
                [$rootId, 'Forms', 'backend/form/index', 'list-ul', 'core', 'form manage'],
                [$rootId, 'Dynamic content', 'backend/dynamic-content/index', 'puzzle-piece', 'core', 'content manage'],
                [$rootId, 'Content Blocks', 'core/backend-chunk/index', 'file-code-o', 'core', 'content manage'],
                [$rootId, 'Sliders', 'backend/slider/index', 'arrows-h', 'core', 'content manage'],
                [$rootId, 'Users', 'user/backend-user/index', 'users', 'core', 'user manage'],
                [$rootId, 'Rbac', 'user/rbac/index', 'lock', 'core', 'user manage'],
                [$rootId, 'Seo', 'seo/manage/index', 'search', 'core', 'seo manage'],
            ]
        );
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => $rootId,
                'name' => 'Images',
                'icon' => 'picture-o',
                'added_by_ext' => 'core',
                'rbac_check' => 'content manage',
                'route' => '',
            ]
        );
        $lastId = $this->db->lastInsertID;
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'added_by_ext', 'rbac_check'],
            [
                [$lastId, 'Thumbnails sizes', 'image/backend-thumbnail-size/index', 'core', 'content manage'],
                [$lastId, 'Create thumbnails', 'image/backend-thumbnail/index', 'core', 'content manage'],
                [$lastId, 'Watermarks', 'image/backend-watermark/index', 'core', 'content manage'],
                [$lastId, 'Broken images', 'image/backend-error-images/index', 'core', 'content manage'],
            ]
        );
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => $rootId,
                'name' => 'Error monitoring',
                'icon' => 'flash',
                'added_by_ext' => 'core',
                'rbac_check' => 'monitoring manage',
                'route' => 'backend/error-monitor/index',
            ]
        );

        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => $rootId,
                'name' => 'Settings',
                'icon' => 'gears',
                'added_by_ext' => 'core',
                'rbac_check' => 'setting manage',
                'route' => '',
            ]
        );
        $lastId = $this->db->lastInsertID;
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'icon', 'added_by_ext', 'rbac_check'],
            [
                [$lastId, 'Tasks', 'background/manage/index', 'tasks', 'core', 'task manage'],
                [$lastId, 'Config', 'config/backend/index', 'gear', 'core', 'setting manage'],
                [$lastId, 'I18n', 'backend/i18n/index', 'language', 'core', 'setting manage'],
                [$lastId, 'Spam Form Checker', 'backend/spam-checker/index', 'send-o', 'core', 'setting manage'],
                [$lastId, 'Backend menu', 'backend/backend-menu/index', 'list-alt', 'core', 'setting manage'],
                [$lastId, 'Data', 'data/file/index', 'database', 'core', 'data manage'],
                [$lastId, 'YML', 'shop/backend-yml/settings', 'code', 'core', 'content manage'],
                [$lastId, 'Api', 'backend/api/index', 'exchange', 'core', 'api manage'],
            ]
        );
        //
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
                    Yii::$app->db->schema->getRawTableName('{{%category_property}}'),
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
                'column_properties_table_name' => Yii::$app->db->schema->getRawTableName('{{%product_property}}'),
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
                                "parameters" => [
                                    "category_group_id" => 1,
                                ],
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
                    'app\properties\handlers\text\TextProperty',
                ],
                [
                    'Select',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    'app\properties\handlers\select\SelectProperty',
                ],
                [
                    'Checkbox',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    'app\properties\handlers\checkbox\CheckboxProperty',
                ],
                [
                    'Text area',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    'app\properties\handlers\textArea\TextAreaProperty',
                ],
                [
                    'File',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    'app\properties\handlers\fileInput\FileInputProperty',
                ],
                [
                    'Hidden',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    'app\properties\handlers\hidden\HiddenProperty',
                ],
                [
                    'Redactor',
                    'frontend-render',
                    'frontend-edit',
                    'backend-render',
                    'backend-edit',
                    'app\properties\handlers\redactor\RedactorProperty',
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
                'view' => 'default',
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
                'name' => Yii::t('app', 'Catalog'),
                'title' => Yii::t('app', 'Catalog'),
                'h1' => Yii::t('app', 'Catalog'),
                'meta_description' => Yii::t('app', 'Catalog'),
                'breadcrumbs_label' => Yii::t('app', 'Catalog'),
                'slug' => 'catalog',
                'slug_compiled' => 'catalog',
            ]
        );
        $this->batchInsert(
            ShippingOption::tableName(),
            ['name', 'description', 'price_from', 'price_to', 'cost', 'sort', 'active'],
            [
                [
                    Yii::t('app', 'Self-pickup'),
                    '',
                    '0',
                    '0',
                    '0',
                    '1',
                    '1',
                ],
                [
                    Yii::t('app', 'Delivery of mail'),
                    '',
                    '0',
                    '0',
                    '100',
                    '2',
                    '1',
                ],
            ]
        );
        $this->batchInsert(
            PaymentType::tableName(),
            ['name', 'class', 'params', 'active', 'sort'],
            [
                [
                    Yii::t('app', 'Cash'),
                    app\components\payment\CashPayment::className(),
                    '[]',
                    '1',
                    '1'
                ],
                [
                    'Robokassa',
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
                [
                    'Platron',
                    app\modules\shop\models\PaymentType::tableName(),
                    Json::encode(
                        [
                            'merchantId' => '',
                            'secretKey' => '',
                            'strCurrency' => 'RUR',
                            'merchantUrl' => 'www.platron.ru',
                            'merchantScriptName' => 'payment.php'
                        ]
                    ),
                    0,
                    13
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
                ['admin', 'data manage'],
                ['admin', 'setting manage'],
            ]
        );

        // demo data
        //
        //
        //
        $this->insert(
            Task::tableName(),
            [
                'action' => 'seo/sitemap/generate-sitemap',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'sitemap',
                'cron_expression' => '0-59/15 * * * *',
            ]
        );
        $this->insert(
            Task::tableName(),
            [
                'action' => 'errornotifier/notify',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'ErrorMonitor notifier',
                'cron_expression' => '*/1 * * * *',
                'status' => 'ACTIVE',
            ]
        );
        $this->batchInsert(
            ProductListingSort::tableName(),
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
        $this->batchInsert(
            SliderHandler::tableName(),
            [
                'name',
                'slider_widget',
                'slider_edit_view_file',
                'edit_model',
            ],
            [
                [
                    'Bootstrap 3 carousel',
                    'app\slider\sliders\bootstrap3\Bootstrap3CarouselWidget',
                    '@app/slider/sliders/bootstrap3/views/edit',
                    'app\slider\sliders\bootstrap3\models\EditModel',
                ],
                [
                    'Slick',
                    'app\slider\sliders\slick\SlickCarouselWidget',
                    '@app/slider/sliders/slick/views/edit',
                    'app\slider\sliders\slick\models\EditModel',
                ],
            ]
        );
        $this->batchInsert(
            Slider::tableName(),
            [
                'name',
                'slider_handler_id',
                'image_width',
                'image_height',
            ],
            [
                ['Example carousel', 1, 900, 350],
            ]
        );
        $this->batchInsert(
            Slide::tableName(),
            [
                'slider_id',
                'sort_order',
                'image',
                'link',
            ],
            [
                [1, 1, 'http://st-1.dotplant.ru/img/dotplant-slider-demo/slide-1.jpg', '#1'],
                [1, 2, 'http://st-1.dotplant.ru/img/dotplant-slider-demo/slide-2.jpg', '#2'],
                [1, 3, 'http://st-1.dotplant.ru/img/dotplant-slider-demo/slide-3.jpg', '#3'],
            ]
        );
        $this->insert(
            CurrencyRateProvider::tableName(),
            [
                'name' => 'Google Finance',
                'class_name' => 'Swap\\Provider\\GoogleFinanceProvider',
            ]
        );
        $this->insert(
            CurrencyRateProvider::tableName(),
            [
                'name' => 'Cbr Finance',
                'class_name' => 'app\\components\\swap\\provider\\CbrFinanceProvider',
            ]
        );
        $this->insert(
            Currency::tableName(),
            [
                'name' => 'Ruble',
                'iso_code' => 'RUB',
                'is_main' => 1,
                'format_string' => '# руб.',
                'intl_formatting' => 0,
            ]
        );
        $this->insert(
            Currency::tableName(),
            [
                'name' => 'US Dollar',
                'iso_code' => 'USD',
                'convert_nominal' => 1,
                'convert_rate' => 62.8353,
                'sort_order' => 1,
                'format_string' => '$ #',
                'thousands_sep' => '.',
                'dec_point' => ',',
            ]
        );
        $this->insert(
            Currency::tableName(),
            [
                'name' => 'Euro',
                'iso_code' => 'EUR',
                'convert_rate' => 71.3243,
                'format_string' => '&euro; #',
            ]
        );
        $this->insert(
            Task::tableName(),
            [
                'action' => 'currency/update',
                'type' => 'REPEAT',
                'initiator' => 1,
                'name' => 'Currency update',
                'cron_expression' => '0 0 * * *',
            ]
        );
        $this->insert(
            Country::tableName(),
            [
                'name' => 'Россия',
                'iso_code' => 'RUS',
                'sort_order' => 0,
                'slug' => 'rossiya',
            ]
        );
        $this->insert(
            Country::tableName(),
            [
                'name' => 'USA',
                'iso_code' => 'USA',
                'sort_order' => 1,
                'slug' => 'usa',
            ]
        );
        $this->insert(
            City::tableName(),
            [
                'name' => 'Москва',
                'slug' => 'moscow',
                'country_id' => 1,
            ]
        );
        $this->insert(
            City::tableName(),
            [
                'name' => 'Санкт-Петербург',
                'slug' => 'spb',
                'country_id' => 1,
            ]
        );
        $this->insert(
            City::tableName(),
            [
                'name' => 'New York',
                'slug' => 'ny',
                'country_id' => 2,
            ]
        );
        $this->insert(
            Warehouse::tableName(),
            [
                'name' => 'Main warehouse',
                'country_id' => 1,
                'city_id' => 1,
                'address' => 'Kremlin',
            ]
        );
        $this->insert(
            WarehousePhone::tableName(),
            [
                'name' => 'Sales',
                'warehouse_id' => 1,
                'phone' => '+7 (495) 123-45-67',
            ]
        );
        $this->insert(
            WarehouseEmail::tableName(),
            [
                'name' => 'Sales',
                'warehouse_id' => 1,
                'email' => 'moscow@example.com',
            ]
        );
        $this->insert(
            WarehouseOpeninghours::tableName(),
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
            Warehouse::tableName(),
            [
                'name' => 'Second warehouse',
                'country_id' => 2,
                'city_id' => 3,
                'address' => 'The WallStreet hidden warehouse',
            ]
        );
        $this->insert(
            WarehousePhone::tableName(),
            [
                'name' => 'Sales',
                'warehouse_id' => 2,
                'phone' => '+1 800 1-WAREHOUSE-1',
            ]
        );
        $this->insert(
            WarehouseEmail::tableName(),
            [
                'name' => 'Sales',
                'warehouse_id' => 2,
                'email' => 'nyc@example.com',
            ]
        );
        $this->insert(
            WarehouseOpeninghours::tableName(),
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
                    'Property',
                    Property::className(),
                    Yii::$app->db->schema->getRawTableName(Property::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%property_property}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_value_category}}'),
                    'slug',
                ],
                [
                    'PropertyStaticValues',
                    PropertyStaticValues::className(),
                    Yii::$app->db->schema->getRawTableName(PropertyStaticValues::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_properties}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_static_value_category}}'),
                    'slug',
                ],
            ]
        );
        $clearTask = new Task;
        $clearTask->setAttributes(
            [
                'action' => 'background/tasks/clear-old-notifications',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Clear old notify messages',
                'cron_expression' => '*/1 * * * *',
                'status' => 'ACTIVE',
            ]
        );
        $clearTask->save();
        $spamTask = new Task;
        $spamTask->setAttributes(
            [
                'action' => 'submissions/mark-spam',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Mark spam submissions as deleted',
                'cron_expression' => '* * */1 * *',
                'status' => 'ACTIVE',
            ]
        );
        $spamTask->save();
        $clearTask = new Task;
        $clearTask->setAttributes(
            [
                'action' => 'submissions/clear-deleted',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Clear deleted submissions',
                'cron_expression' => '* * */3 * *',
                'status' => 'ACTIVE',
            ]
        );
        $clearTask->save();
        $this->batchInsert(
            SpamChecker::tableName(),
            ['behavior', 'name', 'author_field', 'content_field'],
            [
                ['app\\behaviors\\spamchecker\\AkismetSpamChecker', 'Akismet', 'comment_author', 'comment_content'],
            ]
        );
        $this->insert(
            ThumbnailSize::tableName(),
            [
                'width' => 80,
                'height' => 80
            ]
        );
        $this->insert(
            Task::tableName(),
            [
                'action' => 'images/check-broken',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Check broken images',
                'cron_expression' => '* */1 * * *',
            ]
        );
        $this->insert(
            ContentDecorator::tableName(),
            [
                'added_by_ext' => 'core',
                'post_decorator' => 0,
                'class_name' => 'app\modules\core\decorators\ContentBlock',
            ]
        );

        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'product_page_showed',
                'event_class_name' => 'app\\modules\\shop\\events\\ProductPageShowed',
                'event_description' => 'Product page is showed to user',
                'documentation_link' => '',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'product_showed_in_list',
                'event_class_name' => 'app\\modules\\shop\\events\\ProductShowedInList',
                'event_description' => 'Product is showed in product listing(shop/product/list)',
                'documentation_link' => '',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'product_category_listed',
                'event_class_name' => 'app\\modules\\shop\\events\\ProductCategoryListed',
                'event_description' => 'Category is listed by shop/product/list as last_category_id.',
                'documentation_link' => '',
            ]
        );
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => 1,
                'sort_order' => 1,
                'handler_class_name' => 'app\modules\shop\helpers\LastViewedProducts',
                'handler_function_name' => 'handleProductShowed',
                'non_deletable' => 1,
                'triggering_type' => EventTriggeringHelper::TYPE_APPLICATION,
            ]
        );

        $this->insert(
            ExtensionTypes::tableName(),
            [
                'name' => 'Theme',
            ]
        );
        $this->insert(
            ExtensionTypes::tableName(),
            [
                'name' => 'Module',
            ]
        );
        $this->insert(
            ExtensionTypes::tableName(),
            [
                'name' => 'Frontend widget',
            ]
        );
        $this->insert(
            ExtensionTypes::tableName(),
            [
                'name' => 'Dashboard widget',
            ]
        );
        $this->insert(
            ExtensionTypes::tableName(),
            [
                'name' => 'Backend input widget',
            ]
        );
        $this->batchInsert(
            DiscountType::tableName(),
            [
                'name', 'class', 'checking_class', 'add_view'
            ],
            [
                [
                    'Discount Code', 'app\modules\shop\models\DiscountCode', 'Order', '@app/modules/shop/views/backend-discount/_discount_code'
                ],
                [
                    'Category Discount', 'app\modules\shop\models\CategoryDiscount', 'OrderItem', '@app/modules/shop/views/backend-discount/_category_discount'
                ],
                [
                    'User Discount', 'app\modules\shop\models\UserDiscount', 'Order', '@app/modules/shop/views/backend-discount/_user_discount'
                ],
                [
                    'Order Discount', 'app\modules\shop\models\OrderDiscount', 'Order', '@app/modules/shop/views/backend-discount/_order_discount'
                ],
                [
                    'Product Discount', 'app\modules\shop\models\ProductDiscount', 'OrderItem', '@app/modules/shop/views/backend-discount/_product_discount'
                ],
            ]
        );

        $this->batchInsert(
            SpecialPriceList::tableName(),
            [
                'object_id',
                'class',
                'sort_order',
                'type',
                'handler',
            ],
            [
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id,
                    'app\modules\shop\helpers\PriceHandlers',
                    5,
                    'core',
                    'getCurrencyPriceProduct',
                ],
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Order::className())->id,
                    'app\modules\shop\helpers\PriceHandlers',
                    10,
                    'delivery',
                    'getDeliveryPriceOrder',
                ],
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id,
                    'app\modules\shop\helpers\PriceHandlers',
                    15,
                    'discount',
                    'getDiscountPriceProduct',
                ],
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Order::className())->id,
                    'app\modules\shop\helpers\PriceHandlers',
                    20,
                    'discount',
                    'getDiscountPriceOrder',
                ],

            ]
        );
        $this->insert(
            Object::tableName(),
            [
                'name' => 'Customer',
                'object_class' => 'app\modules\shop\models\Customer',
                'object_table_name' => 'customer',
                'column_properties_table_name' => 'customer_property',
                'eav_table_name' => 'customer_eav',
                'categories_table_name' => 'customer_category',
                'link_slug_category' => 'customer_category_slug',
                'link_slug_static_value' => 'customer_slug_static',
                'object_slug_attribute' => 'slug',
            ]
        );
        $this->insert(
            Object::tableName(),
            [
                'name' => 'Contragent',
                'object_class' => 'app\modules\shop\models\Contragent',
                'object_table_name' => 'contragent',
                'column_properties_table_name' => 'contragent_property',
                'eav_table_name' => 'contragent_eav',
                'categories_table_name' => 'contragent_category',
                'link_slug_category' => 'contragent_category_slug',
                'link_slug_static_value' => 'contragent_slug_static',
                'object_slug_attribute' => 'slug',
            ]
        );
        $this->insert(
            Object::tableName(),
            [
                'name' => 'OrderDeliveryInformation',
                'object_class' => 'app\modules\shop\models\OrderDeliveryInformation',
                'object_table_name' => 'order_delivery_information',
                'column_properties_table_name' => 'order_delivery_information_property',
                'eav_table_name' => 'order_delivery_information_eav',
                'categories_table_name' => 'order_delivery_information_category',
                'link_slug_category' => 'order_delivery_information_category_slug',
                'link_slug_static_value' => 'order_delivery_information_slug_static',
                'object_slug_attribute' => 'slug',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stage_customer',
                'event_class_name' => 'app\modules\shop\events\StageCustomer',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleStageCustomer',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stage_delivery',
                'event_class_name' => 'app\modules\shop\events\StageDelivery',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleStageDelivery',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stage_payment',
                'event_class_name' => 'app\modules\shop\events\StagePayment',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleStagePayment',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stage_payment_pay',
                'event_class_name' => 'app\modules\shop\events\StagePaymentPay',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleStagePaymentPay',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stageleaf_customer',
                'event_class_name' => 'app\modules\shop\events\StageLeafCustomer',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleCustomer',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stageleaf_payment_choose',
                'event_class_name' => 'app\modules\shop\events\StageLeafPayment',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handlePayment',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stageleaf_delivery_choose',
                'event_class_name' => 'app\modules\shop\events\StageLeafDelivery',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleDelivery',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stageleaf_payment_pay',
                'event_class_name' => 'app\modules\shop\events\StageLeafPaymentPay',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handlePaymentPay',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stageleaf_manager_process',
                'event_class_name' => 'app\modules\shop\events\StageLeafManagerProcess',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleManagerProcess',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_calculate',
                'event_class_name' => 'app\modules\shop\events\OrderCalculateEvent',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\PriceHandlers',
                'handler_function_name' => 'handleSaveDiscounts',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => -5,
                'handler_class_name' => 'app\modules\shop\helpers\PriceHandlers',
                'handler_function_name' => 'handleSaveDelivery',
                'is_active' => 1,
                'non_deletable' => 0,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            OrderStage::tableName(),
            [
                'name' => 'customer',
                'name_frontend' => Yii::t('app', 'Your information'),
                'name_short' => 'customer',
                'is_initial' => 1,
                'is_buyer_stage' => 1,
                'become_non_temporary' => 0,
                'is_in_cart' => 1,
                'immutable_by_user' => 0,
                'immutable_by_manager' => 0,
                'immutable_by_assigned' => 0,
                'reach_goal_ym' => '',
                'reach_goal_ga' => '',
                'event_name' => 'order_stage_customer',
                'view' => '@app/modules/shop/views/cart/stages/name.php',
            ]
        );
        $stageCustomer = $this->db->lastInsertID;
        $this->insert(
            OrderStage::tableName(),
            [
                'name' => 'delivery',
                'name_frontend' => Yii::t('app', 'Delivery'),
                'name_short' => 'delivery',
                'is_initial' => 0,
                'is_buyer_stage' => 1,
                'become_non_temporary' => 0,
                'is_in_cart' => 1,
                'immutable_by_user' => 0,
                'immutable_by_manager' => 0,
                'immutable_by_assigned' => 0,
                'reach_goal_ym' => '',
                'reach_goal_ga' => '',
                'event_name' => 'order_stage_delivery',
                'view' => '@app/modules/shop/views/cart/stages/delivery.php',
            ]
        );
        $stageDelivery = $this->db->lastInsertID;
        $this->insert(
            OrderStage::tableName(),
            [
                'name' => 'payment',
                'name_frontend' => Yii::t('app', 'Payment method selection'),
                'name_short' => 'payment',
                'is_initial' => 0,
                'is_buyer_stage' => 1,
                'become_non_temporary' => 0,
                'is_in_cart' => 1,
                'immutable_by_user' => 0,
                'immutable_by_manager' => 0,
                'immutable_by_assigned' => 0,
                'reach_goal_ym' => '',
                'reach_goal_ga' => '',
                'event_name' => 'order_stage_payment',
                'view' => '@app/modules/shop/views/cart/stages/payment.php',
            ]
        );
        $stagePayment = $this->db->lastInsertID;
        $this->insert(
            OrderStage::tableName(),
            [
                'name' => 'payment pay',
                'name_frontend' => Yii::t('app', 'Payment'),
                'name_short' => 'payment pay',
                'is_initial' => 0,
                'is_buyer_stage' => 0,
                'become_non_temporary' => 1,
                'is_in_cart' => 0,
                'immutable_by_user' => 0,
                'immutable_by_manager' => 0,
                'immutable_by_assigned' => 0,
                'reach_goal_ym' => '',
                'reach_goal_ga' => '',
                'event_name' => 'order_stage_payment_pay',
                'view' => '@app/modules/shop/views/cart/stages/pay.php',
            ]
        );
        $stagePaymentPay = $this->db->lastInsertID;
        $this->insert(
            OrderStageLeaf::tableName(),
            [
                'stage_from_id' => $stageCustomer,
                'stage_to_id' => $stageDelivery,
                'sort_order' => 0,
                'button_label' => Yii::t('app', 'Delivery method selection'),
                'button_css_class' => 'btn btn-primary',
                'notify_manager' => 0,
                'notify_new_assigned_user' => 0,
                'role_assignment_policy' => 'random',
                'event_name' => 'order_stageleaf_customer',
            ]
        );
        $this->insert(
            OrderStageLeaf::tableName(),
            [
                'stage_from_id' => $stageDelivery,
                'stage_to_id' => $stagePayment,
                'sort_order' => 0,
                'button_label' => Yii::t('app', 'Payment method selection'),
                'button_css_class' => 'btn btn-primary',
                'notify_manager' => 0,
                'notify_new_assigned_user' => 0,
                'role_assignment_policy' => 'random',
                'event_name' => 'order_stageleaf_delivery_choose',
            ]
        );
        $this->insert(
            OrderStageLeaf::tableName(),
            [
                'stage_from_id' => $stagePayment,
                'stage_to_id' => $stagePaymentPay,
                'sort_order' => 0,
                'button_label' => Yii::t('app', 'Go to payment'),
                'button_css_class' => 'btn btn-success',
                'notify_manager' => 1,
                'assign_to_user_id' => 0,
                'assign_to_role' => null,
                'notify_new_assigned_user' => 0,
                'role_assignment_policy' => 'random',
                'event_name' => 'order_stageleaf_payment_choose',
            ]
        );
        $this->insert(
            OrderStage::tableName(),
            [
                'name' => 'final',
                'name_frontend' => Yii::t('app', 'Order complete'),
                'name_short' => 'final',
                'is_initial' => 0,
                'is_buyer_stage' => 0,
                'become_non_temporary' => 0,
                'is_in_cart' => 0,
                'immutable_by_user' => 1,
                'immutable_by_manager' => 1,
                'immutable_by_assigned' => 1,
                'reach_goal_ym' => '',
                'reach_goal_ga' => '',
                'event_name' => 'order_stage_final',
                'view' => '',
            ]
        );
        $stage = $this->db->lastInsertID;
        $this->insert(
            OrderStageLeaf::tableName(),
            [
                'stage_from_id' => $stage,
                'stage_to_id' => $stagePaymentPay,
                'sort_order' => 0,
                'button_label' => Yii::t('app', 'Order complete'),
                'button_css_class' => 'btn btn-primary',
                'notify_manager' => 0,
                'notify_new_assigned_user' => 0,
                'role_assignment_policy' => 'random',
                'event_name' => 'order_stage_leaf_final',
            ]
        );
        $this->insert(
            Measure::tableName(),
            [
                'name' => Yii::t('app', 'Pieces'),
                'symbol' => Yii::t('app', 'pcs'),
                'nominal' => 1,
            ]
        );
        $this->batchInsert(
            Configurable::tableName(),
            ['module', 'sort_order', 'section_name', 'display_in_config'],
            [
                ['core', '1', 'Core', '1'],
                ['DefaultTheme', '2', 'Default Theme', '1'],
                ['shop', '3', 'Shop', '1'],
                ['user', '4', 'Users & Roles', '1'],
                ['page', '5', 'Pages', '1'],
                ['image', '6', 'Images', '1'],
                ['seo', '7', 'SEO', '1'],
                ['backend', '8', 'Backend', '1'],
                ['review', '9', 'Reviews', '1'],
                ['data', '10', 'Data import/export', '1'],
                ['background', 17, 'Background tasks', 1],
            ]
        );
        $this->insert(
            \app\modules\seo\models\Robots::tableName(),
            [
                'key' => 'robots.txt',
                'value' => "User-agent: *\nDisallow: /cabinet\n",
            ]
        );
        $cacheLifetime = 86400;
        $baseParts = [
            [ // 1
                'name' => Yii::t('app', 'Pre-Header'),
                'key' => 'pre-header',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
                'cache_tags' => '\app\widgets\navigation\models\Navigation',
            ],
            [ // 2
                'name' => Yii::t('app', 'Header'),
                'key' => 'header',
                'multiple_widgets' => 0,
                'is_cacheable' => 0,
                'cache_tags' => '\app\widgets\navigation\models\Navigation',
            ],
            [ // 3
                'name' => Yii::t('app', 'Post-Header'),
                'key' => 'post-header',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
                'cache_tags' => '\app\widgets\navigation\models\Navigation',
            ],
            [ // 4
                'name' => Yii::t('app', 'Before content'),
                'key' => 'before-content',
                'multiple_widgets' => 1,
            ],
            [ // 5
                'name' => Yii::t('app', 'Left sidebar'),
                'key' => 'left-sidebar',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 6
                'name' => Yii::t('app', 'Before inner-content'),
                'key' => 'before-inner-content',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 7
                'name' => Yii::t('app', 'After inner-content'),
                'key' => 'after-inner-content',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 8
                'name' => Yii::t('app', 'Right sidebar'),
                'key' => 'right-sidebar',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 9
                'name' => Yii::t('app', 'Pre-footer'),
                'key' => 'pre-footer',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
            ],
            [ // 10
                'name' => Yii::t('app', 'Footer'),
                'key' => 'footer',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
            ],
            [ // 11
                'name' => Yii::t('app', 'Post-footer'),
                'key' => 'post-footer',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
            ],
        ];
        $this->bulkInsert(ThemeParts::tableName(), $baseParts);
        $baseWidgets = [
            [
                // 1
                'name' => Yii::t('app', '1-row header with logo, nav and popup cart'),
                'widget' => 'app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart\Widget',
                'cache_lifetime' => $cacheLifetime,
                'cache_vary_by_session' => 1,
                'configuration_json' => '{}',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/OneRowHeaderWithCart/views/_config.php',
            ],
            [
                // 2
                'name' => Yii::t('app', '1-row header with logo, nav'),
                'widget' => 'app\extensions\DefaultTheme\widgets\OneRowHeader\Widget',
                'cache_lifetime' => $cacheLifetime,
                'configuration_json' => '{}',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\OneRowHeader\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/OneRowHeader/views/_config.php',
            ],
            [
                // 3
                'name' => Yii::t('app', 'Slider'),
                'widget' => 'app\extensions\DefaultTheme\widgets\Slider\Widget',
                'cache_lifetime' => $cacheLifetime,
                'configuration_json' => '{}',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\Slider\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/Slider/views/_config.php',
            ],

        ];
        $this->bulkInsert(ThemeWidgets::tableName(), $baseWidgets);
        $applying = [
            [
                'widget_id' => 1,
                'part_id' => 2,
            ],
            [
                'widget_id' => 2,
                'part_id' => 2,
            ],
            [
                'widget_id' => 3,
                'part_id' => 4,
            ],
            [
                'widget_id' => 3,
                'part_id' => 6,
            ],
        ];
        $this->bulkInsert(ThemeWidgetApplying::tableName(), $applying);
        $variations = [
            [ // 1
                'name' => Yii::t('app', 'Main page'),
                'by_url' => '/',
            ],
            [ // 2
                'name' => Yii::t('app', 'Non main page'),
                'by_url' => '/*',
            ],
            [ // 3
                'name' => Yii::t('app', 'Product listing'),
                'by_route' => 'shop/product/list',
            ],
            [ // 4
                'name' => Yii::t('app', 'Product page(show)'),
                'by_route' => 'shop/product/show',
            ],
            [ // 5
                'name' => Yii::t('app', 'Content page listing'),
                'by_route' => 'page/page/list',
            ],
            [ // 6
                'name' => Yii::t('app', 'Content page(show)'),
                'by_route' => 'page/page/show',
            ],
        ];
        $this->bulkInsert(ThemeVariation::tableName(), $variations);
        $activeWidgets = [
            [
                'widget_id' => 1,
                'part_id' => 2,
                'variation_id' => 2,
            ],
            [
                'widget_id' => 1,
                'part_id' => 2,
                'variation_id' => 1,
            ],
            [
                'widget_id' => 3,
                'part_id' => 4,
                'variation_id' => 1,
            ],
            [
                'widget_id' => 3,
                'part_id' => 6,
                'variation_id' => 3,
            ],
        ];
        $this->bulkInsert(ThemeActiveWidgets::tableName(), $activeWidgets);
        $this->insert(
            '{{%theme_widgets}}',
            [
                'name' => Yii::t('app', 'Categories list'),
                'widget' => 'app\extensions\DefaultTheme\widgets\CategoriesList\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\CategoriesList\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/CategoriesList/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 0,
                'cache_tags' => \app\modules\shop\models\Category::className(),
            ]
        );
        $categoriesListWidgetId = $this->db->lastInsertID;
        $this->insert(
            ThemeWidgets::tableName(),
            [
                'name' => Yii::t('app', 'Filter widget'),
                'widget' => 'app\extensions\DefaultTheme\widgets\FilterSets\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\FilterSets\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/FilterSets/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 0,
                'cache_tags' => '',
            ]
        );
        $filterSetsWidget = $this->db->lastInsertID;
        $this->insert(
            ThemeWidgets::tableName(),
            [
                'name' => Yii::t('app', 'Pages list'),
                'widget' => 'app\extensions\DefaultTheme\widgets\PagesList\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\PagesList\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/PagesList/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 1,
                'cache_lifetime' => 86400,
                'cache_tags' => Page::className(),
            ]
        );
        $pagesList = $this->db->lastInsertID;
        $this->insert(
            ThemeWidgets::tableName(),
            [
                'name' => Yii::t('app', 'Content block'),
                'widget' => 'app\extensions\DefaultTheme\widgets\ContentBlock\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\ContentBlock\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/ContentBlock/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 1,
                'cache_lifetime' => 86400,
                'cache_tags' => Page::className(),
            ]
        );
        $contentBlock = $this->db->lastInsertID;
        $allBlocks = [
            $categoriesListWidgetId,
            $filterSetsWidget,
            $pagesList,
            $contentBlock
        ];
        foreach ($allBlocks as $widget_id) {
            // left sidebar
            $this->insert(
                '{{%theme_widget_applying}}',
                [
                    'widget_id' => $widget_id,
                    'part_id' => 5,
                ]
            );
            // right sidebar
            $this->insert(
                '{{%theme_widget_applying}}',
                [
                    'widget_id' => $widget_id,
                    'part_id' => 8,
                ]
            );
        }
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stage_final',
                'event_class_name' => 'app\modules\shop\events\StageFinal',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleStageFinal',
                'is_active' => 1,
                'non_deletable' => 1,
                'triggering_type' => 'application_trigger',
            ]
        );
        $this->insert(
            Events::tableName(),
            [
                'owner_class_name' => 'app\modules\shop\ShopModule',
                'event_name' => 'order_stage_leaf_final',
                'event_class_name' => 'app\modules\shop\events\StageLeafFinal',
                'selector_prefix' => '',
                'event_description' => '',
                'documentation_link' => '',
            ]
        );
        $eventId = $this->db->lastInsertID;
        $this->insert(
            EventHandlers::tableName(),
            [
                'event_id' => $eventId,
                'sort_order' => 0,
                'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
                'handler_function_name' => 'handleFinal',
                'is_active' => 1,
                'non_deletable' => 1,
                'triggering_type' => 'application_trigger',
            ]
        );
        //
        $propertyHandler = PropertyHandler::findOne(
            [
                'name'=>'Text'
            ]
        );
        $form = new \app\models\Form;
        $form->name = 'Review form';
        $form->email_notification_addresses = '';
        $form->email_notification_view = '@app/modules/review/views/review-email-template.php';
        $form->save(false, ['name', 'email_notification_addresses', 'email_notification_view']);
        $propertyGroup = new PropertyGroup;
        $propertyGroup->attributes = [
            'object_id' => $form->object->id,
            'name' => 'Review form additional properties',
            'hidden_group_title' => 1,
        ];
        $propertyGroup->save(true, ['object_id', 'name', 'hidden_group_title']);
        $nameProperty = new Property;
        $nameProperty->attributes = [
            'property_group_id' => $propertyGroup->id,
            'name' => 'Name',
            'key' => 'name',
            'property_handler_id' => $propertyHandler->id,
            'handler_additional_params' => '{}',
            'is_eav' => 1,
        ];
        $nameProperty->save(true, ['property_group_id', 'name', 'key', 'property_handler_id', 'is_eav', 'handler_additional_params']);
        $phoneProperty = new Property;
        $phoneProperty->attributes = [
            'property_group_id' => $propertyGroup->id,
            'name' => 'Phone',
            'key' => 'phone',
            'property_handler_id' => $propertyHandler->id,
            'handler_additional_params' => '{}',
            'is_eav' => 1,
        ];
        $phoneProperty->save(true, ['property_group_id', 'name', 'key', 'property_handler_id', 'is_eav', 'handler_additional_params']);
        $objectPropertyGroup = new ObjectPropertyGroup;
        $objectPropertyGroup->attributes = [
            'object_id' => $form->object->id,
            'object_model_id' => $form->id,
            'property_group_id' => $propertyGroup->id,
        ];
        $objectPropertyGroup->save(true, ['object_id', 'object_model_id', 'property_group_id']);

    }

    public function down()
    {
        echo "No way back. It's a serious CMS which has been made for big and serious projects.\n";
        return false;
    }
}
