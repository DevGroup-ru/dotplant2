<?php

use yii\db\Schema;
use yii\db\Migration;

class m150428_132852_extensions extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%extensions}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
            'packagist_name' => Schema::TYPE_STRING . ' NOT NULL',
            'force_version' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'dev-master\'',
            'type' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'latest_version' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
            'current_package_version_timestamp' => Schema::TYPE_TIMESTAMP . ' NULL',
            'latest_package_version_timestamp' => Schema::TYPE_TIMESTAMP . ' NULL',
            'homepage' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
            'namespace_prefix' => Schema::TYPE_STRING . ' NOT NULL',
        ], $tableOptions);

        $this->createTable('{{%extension_types}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
        ], $tableOptions);

        $this->insert(
            '{{%extension_types}}',
            [
                'name' => 'Theme',
            ]
        );

        $this->insert(
            '{{%extension_types}}',
            [
                'name' => 'Module',
            ]
        );

        $this->insert(
            '{{%extension_types}}',
            [
                'name' => 'Frontend widget',
            ]
        );

        $this->insert(
            '{{%extension_types}}',
            [
                'name' => 'Dashboard widget',
            ]
        );

        $this->insert(
            '{{%extension_types}}',
            [
                'name' => 'Backend input widget',
            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%extensions}}');
        $this->dropTable('{{%extension_types}}');
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
