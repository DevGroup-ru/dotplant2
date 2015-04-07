<?php

use app\models\Config;
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
                ['app\\behaviors\\spamchecker\\YandexSpamChecker', 'Yandex', 'realname', 'body-plain'],
            ]
        );
        $this->update(Config::tableName(), ['key' => 'author_field'], ['key' => 'name']);
        $this->update(Config::tableName(), ['key' => 'content_field'], ['key' => 'content']);
        $this->delete(Config::tableName(), ['key' => 'apikeys']);
        $this->delete(Config::tableName(), ['key' => 'yandexAPIKey']);
        $this->delete(Config::tableName(), ['key' => 'akismetAPIKey']);
        $this->delete(Config::tableName(), ['key' => 'configFieldsParentId']);
    }

    public function down()
    {
        $this->dropTable('{{%spam_checker}}');
        $this->update(Config::tableName(), ['key' => 'name'], ['key' => 'author_field']);
        $this->update(Config::tableName(), ['key' => 'content'], ['key' => 'content_field']);
        $spamCheckerRootId = Config::findOne(['key' => 'spamCheckerConfig'])->id;
        $formInterpretRootId = Config::findOne(['key' => 'interpretFields'])->id;
        $this->batchInsert(
            Config::tableName(),
            ['parent_id', 'name', 'key', 'value', 'path'],
            [
                [
                    $spamCheckerRootId,
                    'Config Fields Parent Id',
                    'configFieldsParentId',
                    $formInterpretRootId,
                    'spamCheckerConfig.configFieldsParentId'
                ],
                [
                    $spamCheckerRootId,
                    'API keys',
                    'apikeys',
                    'API keys',
                    'spamCheckerConfig.apikeys',
                ],
            ]
        );
    }
}
