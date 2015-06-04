<?php

use yii\db\Schema;
use yii\db\Migration;

class m150515_141224_order_customers extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%customer}}',
            [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT 0',
                'first_name' => Schema::TYPE_STRING,
                'middle_name' => Schema::TYPE_STRING,
                'last_name' => Schema::TYPE_STRING,
                'email' => Schema::TYPE_STRING,
                'phone' => Schema::TYPE_STRING,
            ], $tableOptions);

        $this->createTable('{{%contragent}}',
            [
                'id' => Schema::TYPE_PK,
                'customer_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'type' => "ENUM('Individual', 'Self-employed', 'Legal entity') NOT NULL DEFAULT 'Individual'",
            ], $tableOptions);

        $this->createTable('{{%delivery_information}}',
            [
                'id' => Schema::TYPE_PK,
                'contragent_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'country_id' => Schema::TYPE_INTEGER . ' DEFAULT 0',
                'city_id' => Schema::TYPE_INTEGER . ' DEFAULT 0',
                'zip_code' => Schema::TYPE_STRING,
                'address' => Schema::TYPE_TEXT,
            ], $tableOptions);

        $this->createTable('{{%order_delivery_information}}',
            [
                'id' => Schema::TYPE_PK,
                'order_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'shipping_option_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'shipping_price' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'shipping_price_total' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'planned_delivery_date' => Schema::TYPE_DATE,
                'planned_delivery_time' => Schema::TYPE_TIME,
                'planned_delivery_time_range' => Schema::TYPE_STRING,
            ], $tableOptions);


        $this->insert('{{%object}}', [
            'name' => 'Customer',
            'object_class' => 'app\modules\shop\models\Customer',
            'object_table_name' => 'customer',
            'column_properties_table_name' => 'customer_property',
            'eav_table_name' => 'customer_eav',
            'categories_table_name' => 'customer_category',
            'link_slug_category' => 'customer_category_slug',
            'link_slug_static_value' => 'customer_slug_static',
            'object_slug_attribute' => 'slug',
        ]);

        $this->createTable('{{%customer_eav}}',
            [
                'id' => Schema::TYPE_PK,
                'object_model_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'property_group_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'key' => Schema::TYPE_STRING . ' NOT NULL',
                'value' => Schema::TYPE_TEXT,
                'sort_order' => Schema::TYPE_INTEGER,
            ], $tableOptions);

        $this->insert('{{%object}}', [
            'name' => 'Contragent',
            'object_class' => 'app\modules\shop\models\Contragent',
            'object_table_name' => 'contragent',
            'column_properties_table_name' => 'contragent_property',
            'eav_table_name' => 'contragent_eav',
            'categories_table_name' => 'contragent_category',
            'link_slug_category' => 'contragent_category_slug',
            'link_slug_static_value' => 'contragent_slug_static',
            'object_slug_attribute' => 'slug',
        ]);

        $this->createTable('{{%contragent_eav}}',
            [
                'id' => Schema::TYPE_PK,
                'object_model_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'property_group_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'key' => Schema::TYPE_STRING . ' NOT NULL',
                'value' => Schema::TYPE_TEXT,
                'sort_order' => Schema::TYPE_INTEGER,
            ], $tableOptions);

        $this->insert('{{%object}}', [
            'name' => 'OrderDeliveryInformation',
            'object_class' => 'app\modules\shop\models\OrderDeliveryInformation',
            'object_table_name' => 'order_delivery_information',
            'column_properties_table_name' => 'order_delivery_information_property',
            'eav_table_name' => 'order_delivery_information_eav',
            'categories_table_name' => 'order_delivery_information_category',
            'link_slug_category' => 'order_delivery_information_category_slug',
            'link_slug_static_value' => 'order_delivery_information_slug_static',
            'object_slug_attribute' => 'slug',
        ]);

        $this->createTable('{{%order_delivery_information_eav}}',
            [
                'id' => Schema::TYPE_PK,
                'object_model_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'property_group_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'key' => Schema::TYPE_STRING . ' NOT NULL',
                'value' => Schema::TYPE_TEXT,
                'sort_order' => Schema::TYPE_INTEGER,
            ], $tableOptions);

        $this->addColumn('{{%order}}', 'customer_id', Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL DEFAULT 0');
        $this->addColumn('{{%order}}', 'contragent_id', Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL DEFAULT 0');

        $this->dropColumn('{{%order}}', 'shipping_option_id');
        $this->dropColumn('{{%order}}', 'shipping_price');
        $this->dropColumn('{{%order}}', 'total_price_with_shipping');

        // Stage Customer
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stage_customer',
            'event_class_name' => 'app\modules\shop\events\StageCustomer',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleStageCustomer',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage Delivery
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stage_delivery',
            'event_class_name' => 'app\modules\shop\events\StageDelivery',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleStageDelivery',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage Payment
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stage_payment',
            'event_class_name' => 'app\modules\shop\events\StagePayment',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleStagePayment',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage PaymentPay
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stage_payment_pay',
            'event_class_name' => 'app\modules\shop\events\StagePaymentPay',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleStagePaymentPay',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage leaf Customer
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stageleaf_customer',
            'event_class_name' => 'app\modules\shop\events\StageLeafCustomer',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleCustomer',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage leaf Payment
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stageleaf_payment_choose',
            'event_class_name' => 'app\modules\shop\events\StageLeafPayment',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handlePayment',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage leaf Delivery
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stageleaf_delivery_choose',
            'event_class_name' => 'app\modules\shop\events\StageLeafDelivery',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleDelivery',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage leaf PaymentPay
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stageleaf_payment_pay',
            'event_class_name' => 'app\modules\shop\events\StageLeafPaymentPay',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handlePaymentPay',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        // Stage leaf ManagerProcess
        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stageleaf_manager_process',
            'event_class_name' => 'app\modules\shop\events\StageLeafManagerProcess',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleManagerProcess',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);

        $this->insert('{{%order_stage}}', [
            'name' => 'customer',
            'name_frontend' => 'Данные',
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
        ]);
        $stageCustomer = $this->db->lastInsertID;

        $this->insert('{{%order_stage}}', [
            'name' => 'delivery',
            'name_frontend' => 'Доставка',
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
        ]);
        $stageDelivery = $this->db->lastInsertID;

        $this->insert('{{%order_stage}}', [
            'name' => 'payment',
            'name_frontend' => 'Выбор метода оплаты',
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
        ]);
        $stagePayment = $this->db->lastInsertID;

        $this->insert('{{%order_stage}}', [
            'name' => 'payment pay',
            'name_frontend' => 'Оплата',
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
        ]);
        $stagePaymentPay = $this->db->lastInsertID;

        $this->insert('{{%order_stage}}', [
            'name' => 'manager approve',
            'name_frontend' => 'Подтверждение заказа',
            'name_short' => 'manager approve',
            'is_initial' => 0,
            'is_buyer_stage' => 0,
            'become_non_temporary' => 1,
            'is_in_cart' => 0,
            'immutable_by_user' => 1,
            'immutable_by_manager' => 0,
            'immutable_by_assigned' => 0,
            'reach_goal_ym' => '',
            'reach_goal_ga' => '',
            'event_name' => '',
            'view' => '',
        ]);
        $stageManagerApprove = $this->db->lastInsertID;

        $this->insert('{{%order_stage_leaf}}', [
            'stage_from_id' => $stageCustomer,
            'stage_to_id' => $stageDelivery,
            'sort_order' => 0,
            'button_label' => 'Выбор способа доставки',
            'button_css_class' => 'btn btn-primary',
            'notify_manager' => 0,
            'notify_new_assigned_user' => 0,
            'role_assignment_policy' => 'random',
            'event_name' => 'order_stageleaf_customer',
        ]);

        $this->insert('{{%order_stage_leaf}}', [
            'stage_from_id' => $stageDelivery,
            'stage_to_id' => $stagePayment,
            'sort_order' => 0,
            'button_label' => 'Выбор способа оплаты',
            'button_css_class' => 'btn btn-primary',
            'notify_manager' => 0,
            'notify_new_assigned_user' => 0,
            'role_assignment_policy' => 'random',
            'event_name' => 'order_stageleaf_delivery_choose',
        ]);

        $this->insert('{{%order_stage_leaf}}', [
            'stage_from_id' => $stagePayment,
            'stage_to_id' => $stagePaymentPay,
            'sort_order' => 0,
            'button_label' => 'Оплатить заказ',
            'button_css_class' => 'btn btn-success',
            'notify_manager' => 1,
            'assign_to_user_id' => 0,
            'assign_to_role' => null,
            'notify_new_assigned_user' => 0,
            'role_assignment_policy' => 'random',
            'event_name' => 'order_stageleaf_payment_choose',
        ]);

    }

    public function down()
    {
        $this->dropTable('{{%customer}}');
        $this->dropTable('{{%contragent}}');
        $this->dropTable('{{%delivery_information}}');
        $this->dropTable('{{%order_delivery_information}}');
        $this->dropTable('{{%contragent_eav}}');
        $this->dropTable('{{%customer_eav}}');
        $this->dropTable('{{%order_delivery_information_eav}}');

        $this->delete('{{%object}}', ['name' => 'Customer']);
        $this->delete('{{%object}}', ['name' => 'Contragent']);
        $this->delete('{{%object}}', ['name' => 'OrderDeliveryInformation']);

        $this->dropColumn('{{%order}}', 'customer_id');
        $this->dropColumn('{{%order}}', 'contragent_id');

        $this->addColumn('{{%order}}', 'shipping_option_id', 'INT UNSIGNED DEFAULT 0 AFTER `order_stage_id`');
        $this->addColumn('{{%order}}', 'shipping_price', 'FLOAT DEFAULT \'0\' AFTER `total_price`');
        $this->addColumn('{{%order}}', 'total_price_with_shipping', 'FLOAT DEFAULT \'0\' AFTER `shipping_price`');

        return true;
    }
}
?>