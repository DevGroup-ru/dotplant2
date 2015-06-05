<?php

use app\backgroundtasks\models\Task;
use yii\db\Migration;

class m150330_083706_clear_old_notify extends Migration
{
    public function up()
    {
        $crearTask = new Task;
        $crearTask->setAttributes(
            [
                'action' => 'background/notification/clear-old-notifications',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Clear old notify messages',
                'cron_expression' => '*/1 * * * *',
                'status' => 'ACTIVE',
            ]
        );
        $crearTask->save();
    }

    public function down()
    {
        $this->delete(Task::tableName(), ['action' => 'background/notification/clear-old-notifications']);
    }
}
