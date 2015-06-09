<?php

namespace app\backgroundtasks\commands;

use app\backgroundtasks\helpers\BackgroundTasks;
use app\backgroundtasks\models\NotifyMessage;
use app\backgroundtasks\models\Task;
use yii\console\Controller;

/**
 * Class TasksController
 * @package app\backgroundtasks\commands
 * @author evgen-d <flynn068@gmail.com>
 */
class TasksController extends Controller
{

    public function actionIndex()
    {
        $now = time();

        /* @var $event Task[] */
        $event = Task::find()->where(
            [
                'type' => Task::TYPE_EVENT,
                'status' => Task::STATUS_ACTIVE,
            ]
        )->all();

        /* @var $repeat Task[] */
        $repeat = Task::find()->where(
            [
                'type' => Task::TYPE_REPEAT,
                'status' => Task::STATUS_ACTIVE,
            ]
        )->all();

        foreach ($event as $task) {
            $task->run();
        }

        foreach ($repeat as $task) {
            if (BackgroundTasks::checkExpression($now, $task->cron_expression)) {
                $task->setProcess();
            }
        }

        /* @var $process Task[] */
        $process = Task::find()->where(
            [
                'type' => Task::TYPE_REPEAT,
                'status' => Task::STATUS_PROCESS,
            ]
        )->all();

        foreach ($process as $task) {
            $task->run();
        }
    }

    /**
     * Clear notification messages older then set in config
     */
    public function actionClearOldNotifications()
    {
        $time = new \DateTime();
        $days = intval($this->module->daysToStoreNotify);
        $time->sub(new \DateInterval("P{$days}D"));
        NotifyMessage::deleteAll('UNIX_TIMESTAMP(`ts`) < ' . $time->getTimestamp());
    }
}
