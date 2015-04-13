<?php

use yii\db\Schema;
use yii\db\Migration;

class m150409_202926_configurables extends Migration
{
    public function up()
    {
        return false;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%configurable}}', [
            'id' => Schema::TYPE_PK,
            'module' => Schema::TYPE_STRING . ' NOT NULL',
            'preload' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
            'section_name' => Schema::TYPE_STRING . ' NOT NULL',
            'display_in_config' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%configurable}}');
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
