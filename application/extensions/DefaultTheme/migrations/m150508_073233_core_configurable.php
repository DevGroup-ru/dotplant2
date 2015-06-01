<?php

use yii\db\Schema;
use yii\db\Migration;

class m150508_073233_core_configurable extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%configurable}}',
            [
                'module' => 'core',
                'sort_order' => 12,
                'section_name' => 'Core',
                'display_in_config' => 1,
            ]
        );
    }

    public function down()
    {
        $this->delete('{{%configurable}}', ['module' => 'core']);
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
