<?php

use yii\db\Schema;
use yii\db\Migration;

class m150525_081808_order_calculate_handler_delivery extends Migration
{
    public function up()
    {

        $event = \app\modules\core\models\Events::find()
            ->where(
                [
                    'event_name' => 'order_calculate',
                    'event_class_name' => 'app\modules\shop\events\OrderCalculateEvent',
                ]
            )
            ->one();

        $this->insert('{{%event_handlers}}', [
            'event_id' => $event->id,
            'sort_order' => -5,
            'handler_class_name' => 'app\modules\shop\helpers\PriceHandlers',
            'handler_function_name' => 'handleSaveDelivery',
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
                'handler_class_name' => 'app\modules\shop\helpers\PriceHandlers',
                'handler_function_name' => 'handleSaveDelivery'
            ]
        );
    }

}
