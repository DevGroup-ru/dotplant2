<?php

use app\backgroundtasks\models\Task;
use app\models\Submission;
use yii\db\Migration;

class m150907_062636_background_form_sending extends Migration
{

    public function up()
    {
        $this->addColumn(
            Submission::tableName(),
            'sending_status',
            'TINYINT DEFAULT 0'
        );
        Submission::updateAll(['sending_status' => Submission::STATUS_SUCCESS]);
        $this->insert(
            Task::tableName(),
            [
                'action' => 'submissions/send-new',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Send new submissions',
                'cron_expression' => '*/5 * * * *',
            ]
        );
    }

    public function down()
    {
        Task::deleteAll(['action' => 'submissions/send-new']);
        $this->dropColumn(
            Submission::tableName(),
            'sending_status'
        );
    }
}
