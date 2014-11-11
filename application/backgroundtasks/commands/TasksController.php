<?php

namespace app\backgroundtasks\commands;

use yii\console\Controller;
use app\backgroundtasks\helpers\BackgroundTasks;
use app\backgroundtasks\models\Task;

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
}
