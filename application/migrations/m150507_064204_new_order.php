<?php

use app\models\DynamicContent;
use app\models\Object;
use app\models\Route;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use yii\db\Migration;

class m150507_064204_new_order extends Migration
{
    public function up()
    {
        // @todo Need to add indexes to all tables below
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        // Order
        $this->renameColumn(Order::tableName(), 'order_status_id', 'order_stage_id');
        $this->addColumn(Order::tableName(), 'assigned_id', 'INT UNSIGNED DEFAULT NULL AFTER `payment_type_id`');
        $this->addColumn(Order::tableName(), 'tax_id', 'INT UNSIGNED DEFAULT NULL AFTER `assigned_id`');
        $this->addColumn(Order::tableName(), 'update_date', 'TIMESTAMP AFTER `start_date`');
        $this->addColumn(Order::tableName(), 'shipping_price', 'FLOAT DEFAULT \'0\' AFTER `total_price`');
        $this->addColumn(Order::tableName(), 'total_price_with_shipping', 'FLOAT DEFAULT \'0\' AFTER `shipping_price`');
        $this->addColumn(Order::tableName(), 'total_payed', 'FLOAT DEFAULT \'0\' AFTER `total_price_with_shipping`');
        $this->addColumn(Order::tableName(), 'temporary', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1');
        $this->addColumn(Order::tableName(), 'show_price_changed_notification', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');
        // OrderItem
        $this->addColumn(OrderItem::tableName(), 'parent_id', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `id`');
        $this->addColumn(OrderItem::tableName(), 'custom_name', 'VARCHAR(255) AFTER `product_id`');
        $this->addColumn(OrderItem::tableName(), 'price_per_pcs', 'FLOAT NOT NULL DEFAULT \'0\'');
        $this->addColumn(OrderItem::tableName(), 'total_price_without_discount', 'FLOAT NOT NULL DEFAULT \'0\'');
        $this->addColumn(OrderItem::tableName(), 'lock_product_price', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');
        $this->addColumn(OrderItem::tableName(), 'discount_amount', 'FLOAT NOT NULL DEFAULT \'0\'');
        $this->addColumn(OrderItem::tableName(), 'total_price', 'FLOAT NOT NULL DEFAULT \'0\'');
        $this->alterColumn(OrderItem::tableName(), 'quantity', 'FLOAT NOT NULL DEFAULT \'1\'');
        $this->dropColumn(OrderItem::tableName(), 'additional_options');
        // OrderStage
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
        // OrderStageLeaf
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
        // Update Object model data
        $this->update(
            Object::tableName(),
            ['object_class' => 'app\modules\shop\models\Category'],
            ['object_class' => 'app\models\Category']
        );
        $this->update(
            Object::tableName(),
            ['object_class' => 'app\modules\shop\models\Product'],
            ['object_class' => 'app\models\Product']
        );
        $this->update(
            Object::tableName(),
            ['object_class' => 'app\modules\shop\models\Order'],
            ['object_class' => 'app\models\Order']
        );
        // Update DynamicContent model data
        $this->update(DynamicContent::tableName(), ['route' => 'shop/product/list'], ['route' => 'product/list']);
        $this->update(DynamicContent::tableName(), ['route' => 'shop/product/show'], ['route' => 'product/show']);
        // Update Route model data
        $this->update(Route::tableName(), ['route' => 'shop/product/list'], ['route' => 'product/list']);
        $this->update(Route::tableName(), ['route' => 'shop/product/show'], ['route' => 'product/show']);
    }

    public function down()
    {
        echo "m150507_064204_new_order cannot be reverted.\n";
        return false;
    }
}
