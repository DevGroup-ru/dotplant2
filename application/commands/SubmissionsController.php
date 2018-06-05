<?php

namespace app\commands;

use app\models\BaseObject;
use app\models\ObjectStaticValues;
use app\models\Submission;
use app\modules\review\models\Review;
use Yii;
use yii\base\Event;
use yii\console\Controller;

class SubmissionsController extends Controller
{

    const EVENT_SEND_SUBMISSIONS = 'event_send_submission';

    /**
     * Send submissions method
     * @param Submission[] $submissions
     */
    private function sendSubmissions($submissions)
    {
        foreach ($submissions as $submission) {
            echo "Sending {$submission->id}\n";
            if ($submission->form === null) {
                $submission->processed = Submission::STATUS_FATAL_ERROR;
            } else {
                $event = new Event();
                $event->sender = $submission;
                $this->trigger('event_send_submission', $event);
            }
            $submission->save(true, ['sending_status']);
        }
    }

    public function actionMarkSpam()
    {
        Submission::updateAll(['is_deleted' => 1], ['spam' => 1]);
    }

    public function actionClearDeleted()
    {
        $time = new \DateTime();
        $days = Yii::$app->getModule('core')->daysToStoreSubmissions;
        $time->sub(new \DateInterval("P{$days}D"));
        /** @var BaseObject $object */
        $object = BaseObject::getForClass(Submission::className());
        if ($object !== null) {
            $submissionIds = Submission::find()
                ->select(['id'])
                ->where('UNIX_TIMESTAMP(`date_received`) < ' . $time->getTimestamp() . ' AND `is_deleted` = \'1\'')
                ->column();
            Review::deleteAll(['submission_id' => $submissionIds]);
            Yii::$app->db->createCommand()->delete(
                $object->column_properties_table_name,
                ['object_model_id' => $submissionIds]
            );
            Yii::$app->db->createCommand()->delete(
                $object->eav_table_name,
                ['object_model_id' => $submissionIds]
            );
            Yii::$app->db->createCommand()->delete(
                $object->categories_table_name,
                ['object_model_id' => $submissionIds]
            );
            ObjectStaticValues::deleteAll(
                [
                    'object_id' => $object->id,
                    'object_model_id' => $submissionIds,
                ]
            );
            Submission::deleteAll(['id' => $submissionIds]);
        }
    }

    /**
     * Send new submissions action
     * @param bool $sendFailed
     */
    public function actionSendNew($sendFailed = false)
    {
        /** @var Submission[] $submissions */
        $statuses = [
            Submission::STATUS_NEW,
        ];
        if ($sendFailed) {
            $statuses[] = Submission::STATUS_ERROR;
        }
        $submissions = Submission::find()
            ->with('form')
            ->where(
                [
                    'sending_status' => $statuses,
                    'spam' => 0,
                    'is_deleted' => 0
                ]
            )
            ->all();

        $this->sendSubmissions($submissions);
    }

    /**
     * Send failed submissions action
     */
    public function actionSendFailed()
    {
        /** @var Submission[] $submissions */
        $submissions = Submission::find()
            ->with('form')
            ->where(
                [
                    'sending_status' => Submission::STATUS_ERROR,
                    'spam' => 0,
                    'is_deleted' => 0
                ]
            )
            ->all();
        $this->sendSubmissions($submissions, Submission::STATUS_HOPELESS_ERROR);
    }

    /**
     * Mark filed submissions as new
     */
    public function actionReliveFailed()
    {
        Submission::updateAll(
            [
                'sending_status' => Submission::STATUS_NEW,
            ],
            [
                'sending_status' => [Submission::STATUS_ERROR, Submission::STATUS_HOPELESS_ERROR]
            ]
        );
    }
}
