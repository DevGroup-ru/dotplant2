<?php

use yii\db\Schema;
use yii\db\Migration;

class m150522_085644_order_calculate_event extends Migration
{
    public function up()
    {

      //  return false;


        $this->addColumn(
            '{{%special_price_list}}',
            'handler',
            Schema::TYPE_STRING


        );


        $this->update(
            '{{%special_price_list}}',
            [
                'class' => 'app\modules\shop\helpers\PriceHandlers',
                'handler' => 'getCurrencyPriceProduct'
            ],
            [
                'class' => 'app\modules\shop\models\Currency',
                'object_id' => \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id
            ]
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'class' => 'app\modules\shop\helpers\PriceHandlers',
                'handler' => 'getDiscountPriceProduct'
            ],
            [
                'class' => 'app\modules\shop\models\Discount',
                'object_id' => \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id
            ]
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'class' => 'app\modules\shop\helpers\PriceHandlers',
                'handler' => 'getDiscountPriceOrder'
            ],
            [
                'class' => 'app\modules\shop\models\Discount',
                'object_id' => \app\models\Object::getForClass(\app\modules\shop\models\Order::className())->id
            ]
        );

        $this->insert(
            '{{%special_price_list}}',
            [
                'object_id' => \app\models\Object::getForClass(\app\modules\shop\models\Order::className())->id,
                'class' => 'app\modules\shop\helpers\PriceHandlers',
                'active' => 1,
                'sort_order' => 12,
                'handler' => 'getDeliveryPriceOrder',
                'type_id' => (new \yii\db\Query())
                    ->select('id')
                    ->from('{{%special_price_list_type}}')
                    ->where(['key' => 'delivery'])
                    ->scalar()
            ]
        );

        $this->insert('{{%events}}', [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_calculate',
            'event_class_name' => 'app\modules\shop\events\OrderCalculateEvent',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);

        $eventId = $this->db->lastInsertID;

        $this->insert('{{%event_handlers}}', [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\PriceHandlers',
            'handler_function_name' => 'handleSaveDiscounts',
            'is_active' => 1,
            'non_deletable' => 0,
            'triggering_type' => 'application_trigger',
        ]);




    }

    public function down()
    {

        $this->delete(
            '{{%event_handlers}}',
            [
                'handler_function_name' => 'handleSaveDiscounts'
            ]
        );


        $this->dropColumn(
            '{{%special_price_list}}',
            'handler'
        );

        $this->delete(
            '{{%events}}',
            [
                'event_name' => 'order_calculate',
            ]
        );


    }

}
