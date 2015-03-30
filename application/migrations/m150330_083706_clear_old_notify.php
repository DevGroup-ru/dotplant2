<?php

use app\backgroundtasks\models\Task;
use app\models\Config;
use yii\db\Migration;

class m150330_083706_clear_old_notify extends Migration
{
    public function up()
    {
        $errorMonitor = Config::findOne(['key' => 'errorMonitor']);
        $oldNotifyDays = new Config;
        $oldNotifyDays->setAttributes(
            [
                'parent_id' => $errorMonitor->id,
                'name' => 'Store notify history within days',
                'key' => 'daysToStoreNotify',
                'value' => '28',
                'preload' => 1,
            ]
        );
        $oldNotifyDays->save();
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
        echo "m150330_083706_clear_old_notify cannot be reverted.\n";

        return false;
    }
}
