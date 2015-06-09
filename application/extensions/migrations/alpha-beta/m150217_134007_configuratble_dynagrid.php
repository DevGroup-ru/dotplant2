<?php

use yii\db\Schema;
use yii\db\Migration;

class m150217_134007_configuratble_dynagrid extends Migration
{
    public function up()
    {
        $this->createTable('{{%dynagrid}}',[
            'id' => Schema::TYPE_STRING,
            'filter_id' => Schema::TYPE_STRING,
            'sort_id' => Schema::TYPE_STRING,
            'data' => Schema::TYPE_TEXT,
        ]);
        $this->addPrimaryKey('id', '{{%dynagrid}}', 'id');

        $this->createTable('{{%dynagrid_dtl}}',[
            'id' => Schema::TYPE_STRING,
            'category' => Schema::TYPE_STRING,
            'name' => Schema::TYPE_STRING,
            'data' => Schema::TYPE_TEXT,
            'dynagrid_id' => Schema::TYPE_STRING,
        ]);
        $this->addPrimaryKey('id', '{{%dynagrid_dtl}}', 'id');
        $this->createIndex('uniq_dtl', '{{%dynagrid_dtl}}', ['name', 'category', 'dynagrid_id'], true);

    }

    public function down()
    {
        $this->dropTable('{{%dynagrid}}');
        $this->dropTable('{{%dynagrid_dtl}}');
    }
}
