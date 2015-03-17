<?php

use yii\db\Schema;
use yii\db\Migration;

class m150317_070330_backgroundtasks_task_add_options extends Migration
{
    public function up()
    {
        $this->addColumn(
            '{{%backgroundtasks_task}}',
            'options',
            Schema::TYPE_TEXT
        );
    }

    public function down()
    {
        $this->dropColumn('{{%backgroundtasks_task}}', 'options');

        return true;
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
