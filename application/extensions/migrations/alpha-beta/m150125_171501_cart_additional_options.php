<?php

use app\modules\shop\models\OrderItem;
use yii\db\Migration;

class m150125_171501_cart_additional_options extends Migration
{
    public function up()
    {
        $this->addColumn(OrderItem::tableName(), 'additional_options', 'string');
    }

    public function down()
    {
        $this->dropColumn(OrderItem::tableName(), 'additional_options');
    }
}
