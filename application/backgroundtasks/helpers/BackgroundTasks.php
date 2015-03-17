<?php

namespace app\backgroundtasks\helpers;

use app\backgroundtasks\models\Task;
use yii\console\Exception;
use yii\helpers\Json;

/**
 * Class BackgroundTasks
 * @package app\backgroundtasks\helpers
 * @author evgen-d <flynn068@gmail.com>
 */
class BackgroundTasks
{

    /**
     * Check cron expression
     * Return true if the expression matches with current time
     * @param $timestamp
     * @param $cronExpr
     * @return mixed
     */
    public static function checkExpression($timestamp, $cronExpr)
    {
        $strtime = date('r', $timestamp);

        $time = explode(' ', date('i G j n w', strtotime($strtime)));
        $crontab = explode(' ', $cronExpr);

        $expr = [];
        foreach ($crontab as $index => $val) {
            $values = preg_replace(
                [
                    '/^\*$/',
                    '/^(\d+)$/',
                    '/^(\d+)-(\d+)\/(\d+)$/',
                    '/^(\d+)-(\d+)$/',
                    '/^\*\/(\d+)$/'
                ],
                [
                    '(true)',
                    '('.$time[$index].' === $1)',
                    '((($1 <= '.$time[$index].') && ('.$time[$index].' <= $2)) ? ('.$time[$index].'%$3 === 0) : false)',
                    '($1 <= '.$time[$index].') && ('.$time[$index].' <= $2)', '('.$time[$index].'%$1 === 0)'
                ],
                explode(',', $val)
            );
            $expr[] = implode(' || ', $values);
        }

        if ($expr[2] !== '(true)' && $expr[4] !== '(true)') {
            $expr[2] = '('.$expr[2].' || '.$expr[4].')';
            unset($expr[4]);
        }

        return eval('return '.implode(' && ', $expr).';');
    }

    /**
     * Add event task in database
     * @param $params
     * @return bool
     */
    public static function addTask($params, $options = [])
    {
        $task = new Task(['scenario' => 'event']);
        $task->load(['Task' => $params]);
        $task->type = Task::TYPE_EVENT;
        $task->initiator = \Yii::$app->user->id;

        if (!empty($options)) {
            $task->setOptions($options);
        }

        return $task->validate() && $task->save();
    }

    /**
     * Remove Task model
     * @param $id
     * @return int
     */
    public static function removeTask($id)
    {
        return Task::deleteAll('id = :id', [':id' => $id]);
    }

    /**
     * Set task as active
     * @param $id
     * @return int
     */
    public static function setActive($id)
    {
        return Task::updateAll(['status' => Task::STATUS_ACTIVE], 'id = :id', [':id' => $id]);
    }

    /**
     * Set task as stopped
     * @param $id
     * @return int
     */
    public static function setStopped($id)
    {
        return Task::updateAll(['status' => Task::STATUS_STOPPED], 'id = :id', [':id' => $id]);
    }

    /**
     * Get Task data
     * @param $id
     * @param $controller
     * @return mixed
     * @throws Exception
     */
    public static function getData($id, $controller)
    {
        $task = Task::findOne($id);
        if ($task !== null && $task->action == $controller->route) {
            return Json::decode($task->data, true);
        } else {
            throw new Exception("Data not found");
        }
    }
}
