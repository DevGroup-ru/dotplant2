<?php

use yii\db\Schema;
use yii\db\Migration;

class m150416_065047_content_decorators extends Migration
{

    public function up()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%content_decorators}}', [
            'id' => Schema::TYPE_PK,
            'added_by_ext' => Schema::TYPE_STRING . ' NOT NULL',
            'post_decorator' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            'class_name' => Schema::TYPE_STRING . ' NOT NULL',
            'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
        ], $tableOptions);

        $this->insert('{{%content_decorators}}', [
            'added_by_ext' => 'core',
            'post_decorator' => 0,
            'class_name' => 'app\modules\core\decorators\ContentBlock',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%content_decorators}}');
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
