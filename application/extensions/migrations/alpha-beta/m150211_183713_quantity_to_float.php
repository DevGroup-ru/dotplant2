<?php

use yii\db\Schema;
use yii\db\Migration;

class m150211_183713_quantity_to_float extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%order}}', 'items_count', Schema::TYPE_FLOAT . ' UNSIGNED');
        $this->alterColumn('{{%order_item}}', 'quantity', Schema::TYPE_FLOAT . ' UNSIGNED');
        return true;
    }

    public function down()
    {
        $this->alterColumn('{{%order}}', 'items_count', Schema::TYPE_INTEGER . ' UNSIGNED');
        $this->alterColumn('{{%order_item}}', 'quantity', Schema::TYPE_INTEGER . ' UNSIGNED');
    }
}
