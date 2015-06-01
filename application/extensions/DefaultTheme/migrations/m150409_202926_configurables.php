<?php

use yii\db\Schema;
use yii\db\Migration;

class m150409_202926_configurables extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%configurable}}', [
            'id' => Schema::TYPE_PK,
            'module' => Schema::TYPE_STRING . ' NOT NULL',
            'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'section_name' => Schema::TYPE_STRING . ' NOT NULL', // will be translated by yiit
            'display_in_config' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
        ], $tableOptions);


        /*
         * Base config sections:
         *
         * 1. core
         * 2. backend (editor selection, etc.)
         * 3. pages
         * 4. shop
         * 5. users
         * 6. search
         * ??? something else ??
         */

        $this->insert('{{%configurable}}', [
            'module' => 'user',
            'sort_order' => 5,
            'section_name' => 'Users & Roles',
        ]);
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
