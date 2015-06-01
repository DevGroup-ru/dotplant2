<?php

use app\models\Config;
use yii\db\Migration;

class m150317_111229_order_email extends Migration
{
    public function up()
    {
        $shop = Config::findOne(['path' => 'shop']);
        if (!is_null($shop)) {
            $config = new Config;
            $config->attributes = [
                'parent_id' => $shop->id,
                'name' => 'Order e-mail template',
                'key' => 'orderEmailTemplate',
                'value' => '@app/views/cart/order-email-template',
            ];
            $config->save();
            $config = new Config;
            $config->attributes = [
                'parent_id' => $shop->id,
                'name' => 'Client order e-mail template',
                'key' => 'clientOrderEmailTemplate',
                'value' => '@app/views/cart/client-order-email-template',
            ];
            $config->save();
        }
    }

    public function down()
    {
        Config::deleteAll(['path' => ['shop.orderEmailTemplate', 'shop.clientOrderEmailTemplate']]);
    }
}
