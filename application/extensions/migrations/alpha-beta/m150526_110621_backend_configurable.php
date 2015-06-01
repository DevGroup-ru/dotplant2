<?php

use yii\db\Schema;
use yii\db\Migration;

class m150526_110621_backend_configurable extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%configurable}}',
            [
                'module' => 'backend',
                'sort_order' => 12,
                'section_name' => 'Backend',
                'display_in_config' => 1,
            ]
        );
    }

    public function down()
    {
        $this->delete('{{%configurable}}', ['module' => 'backend']);
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
