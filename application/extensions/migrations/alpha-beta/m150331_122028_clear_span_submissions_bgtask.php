<?php

use app\backgroundtasks\models\Task;
use yii\db\Migration;

class m150331_122028_clear_span_submissions_bgtask extends Migration
{
    public function up()
    {
        $spamTask = new Task;
        $spamTask->setAttributes(
            [
                'action' => 'backend/form/mark-spam',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Mark spam submissions as deleted',
                'cron_expression' => '* * */1 * *',
                'status' => 'ACTIVE',
            ]
        );
        $spamTask->save();
        $clearTask = new Task;
        $clearTask->setAttributes(
            [
                'action' => 'backend/form/clear-deleted',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Clear deleted submissions',
                'cron_expression' => '* * */3 * *',
                'status' => 'ACTIVE',
            ]
        );
        $clearTask->save();
    }

    public function down()
    {
        $this->delete(Task::tableName(), ['action' => 'backend/form/mark-spam']);
        $this->delete(Task::tableName(), ['action' => 'backend/form/clear-deleted']);
    }
}
