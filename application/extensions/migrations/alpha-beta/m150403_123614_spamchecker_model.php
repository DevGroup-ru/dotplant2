<?php

use yii\db\Migration;

class m150403_123614_spamchecker_model extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%spam_checker}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'behavior' => 'VARCHAR(255) NOT NULL',
                'api_key' => 'VARCHAR(90) DEFAULT NULL',
                'name' => 'VARCHAR(50) DEFAULT NULL',
                'author_field' => 'VARCHAR(50) DEFAULT NULL',
                'content_field' => 'VARCHAR(50) DEFAULT NULL',
            ],
            $tableOptions
        );
        $this->batchInsert(
            '{{%spam_checker}}',
            ['behavior', 'name', 'author_field', 'content_field'],
            [
                ['app\\behaviors\\spamchecker\\AkismetSpamChecker', 'Akismet', 'comment_author', 'comment_content'],
            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%spam_checker}}');
    }
}
