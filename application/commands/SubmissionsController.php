<?php

namespace app\commands;


use app\models\Config;
use app\models\Submission;
use Yii;
use yii\console\Controller;

class SubmissionsController extends Controller
{
    public function actionMarkSpam()
    {
        Submission::updateAll(['is_deleted' => 1], "spam = '" . Yii::$app->formatter->asBoolean(true) . "'");
    }

    public function actionClearDeleted()
    {
        $time = new \DateTime();
        $days = Config::getValue('submissions.daysToStoreSubmissions', 28);
        $time->sub(new \DateInterval("P{$days}D"));
        Submission::deleteAll(
            'UNIX_TIMESTAMP(`date_received`) < ' . $time->getTimestamp() . ' AND `is_deleted` = \'1\''
        );
    }
}