<?php

use yii\db\Schema;
use yii\db\Migration;

class m150601_111734_order_in_cart extends Migration
{
    public function up()
    {
        $this->addColumn(\app\modules\shop\models\Order::tableName(),
            'in_cart', Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0'
        );
    }

    public function down()
    {
        echo "m150601_111734_order_in_cart cannot be reverted.\n";

        return false;
    }
}
?>