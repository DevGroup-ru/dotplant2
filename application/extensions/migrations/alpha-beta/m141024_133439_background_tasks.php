<?php

use app\backgroundtasks\models\NotifyMessage;
use app\backgroundtasks\models\Task;
use yii\db\Migration;

class m141024_133439_background_tasks extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            Task::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'action' => 'VARCHAR(255) NOT NULL',
                'type' => " enum('EVENT','REPEAT') NOT NULL DEFAULT 'EVENT'",
                'initiator' => 'INT UNSIGNED UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255) NOT NULL',
                'description' => 'TEXT DEFAULT NULL',
                'params' => 'TEXT DEFAULT NULL',
                'init_event' => 'VARCHAR(255) DEFAULT NULL',
                'cron_expression' => 'VARCHAR(255) DEFAULT NULL',
                'status' => " enum('ACTIVE','STOPPED','RUNNING','FAILED','COMPLETED') NOT NULL DEFAULT 'ACTIVE'",
                'ts' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'fall_counter' => 'TINYINT UNSIGNED NOT NULL DEFAULT\'0\'',
                'KEY `name` (`name`)',
            ],
            $tableOptions
        );
        $this->createTable(
            NotifyMessage::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'task_id' => 'INT UNSIGNED NOT NULL',
                'result_status' => 'enum(\'SUCCESS\',\'FAULT\') NOT NULL DEFAULT \'SUCCESS\'',
                'result' => 'TEXT DEFAULT NULL',
                'ts' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ],
            'ENGINE=InnoDB  DEFAULT CHARSET=utf8'
        );
        $this->insert(
            Task::tableName(),
            [
                'action' => 'seo/sitemap/generate-sitemap',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'sitemap',
                'cron_expression' => '0-59/15 * * * *',
            ]
        );
        $this->insert(
            Task::tableName(),
            [
                'action' => 'errornotifier/notify',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'ErrorMonitor notifier',
                'cron_expression' => '*/1 * * * *',
                'status' => 'ACTIVE',
            ]
        );
    }

    public function down()
    {
        $this->dropTable(NotifyMessage::tableName());
        $this->dropTable(Task::tableName());
    }
}
