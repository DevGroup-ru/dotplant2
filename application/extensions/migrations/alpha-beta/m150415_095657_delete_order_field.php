<?php

use yii\db\Schema;
use yii\db\Migration;
use app\modules\shop\models\Order;

class m150415_095657_delete_order_field extends Migration
{
    public function up()
    {
        $this->addColumn(Order::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
    }

    public function down()
    {
        $this->dropColumn(Order::tableName(), 'is_deleted');
    }
}
