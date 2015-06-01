<?php

use app\backgroundtasks\models\Task;
use app\models\Config;
use yii\db\Migration;

class m150408_140510_submission_clear extends Migration
{
    public function up()
    {
        $this->update(
            Task::tableName(),
            ['action' => 'background/tasks/clear-old-notifications'],
            ['action' => 'background/notification/clear-old-notifications']
        );
        $this->update(
            Task::tableName(),
            ['action' => 'submissions/mark-spam'],
            ['action' => 'backend/form/mark-spam']
        );
        $this->update(
            Task::tableName(),
            ['action' => 'submissions/clear-deleted'],
            ['action' => 'backend/form/clear-deleted']
        );
        $this->insert(
            Config::tableName(),
            [
                'parent_id' => 0,
                'name' => 'Submissions',
                'key' => 'submissions',
                'value' => '',
                'path' => 'submissions',
                'preload' => 0
            ]
        );
        $this->insert(
            Config::tableName(),
            [
                'parent_id' => Yii::$app->db->lastInsertID,
                'name' => 'Store deleted submissions within days',
                'key' => 'daysToStoreSubmissions',
                'value' => 28,
                'path' => 'submissions.daysToStoreSubmissions',
                'preload' => 0
            ]
        );
    }

    public function down()
    {
        $this->update(
            Task::tableName(),
            ['action' => 'background/notification/clear-old-notifications'],
            ['action' => 'background/tasks/clear-old-notifications']
        );
        $this->update(
            Task::tableName(),
            ['action' => 'backend/form/mark-spam'],
            ['action' => 'submissions/mark-spam']
        );
        $this->update(
            Task::tableName(),
            ['action' => 'backend/form/clear-deleted'],
            ['action' => 'submissions/clear-deleted']
        );
        $this->delete(Config::tableName(), ['key' => 'submissions']);
        $this->delete(Config::tableName(), ['key' => 'daysToStoreSubmissions']);
    }
}
