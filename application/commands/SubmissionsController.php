<?php

namespace app\commands;

use app\models\Submission;
use Yii;
use yii\console\Controller;

class SubmissionsController extends Controller
{
    public function actionMarkSpam()
    {
        Submission::updateAll(['is_deleted' => 1], ['spam' => 1]);
    }

    public function actionClearDeleted()
    {
        $time = new \DateTime();
        $days = Yii::$app->getModule('core')->daysToStoreSubmissions;
        $time->sub(new \DateInterval("P{$days}D"));
        Submission::deleteAll(
            'UNIX_TIMESTAMP(`date_received`) < ' . $time->getTimestamp() . ' AND `is_deleted` = \'1\''
        );
    }
}
