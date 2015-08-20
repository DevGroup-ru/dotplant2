<?php

use yii\db\Schema;
use yii\db\Migration;

class m150820_063318_measure_sort_order extends Migration
{

    public function up()
    {
        $this->addColumn('{{%measure}}', 'sort_order', Schema::TYPE_INTEGER . ' DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%measure}}', 'sort_order');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
