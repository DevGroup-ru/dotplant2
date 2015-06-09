<?php

use app\backgroundtasks\models\Task;
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
    }
}
