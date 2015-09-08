<?php

namespace app\commands;

use app\models\Submission;
use Yii;
use yii\console\Controller;

class SubmissionsController extends Controller
{
    /**
     * Send submissions method
     * @param Submission[] $submissions
     * @param int $errorStatus
     */
    private function sendSubmissions($submissions, $errorStatus = Submission::STATUS_ERROR)
    {
        foreach ($submissions as $submission) {
            if ($submission->form === null) {
                $submission->processed = Submission::STATUS_FATAL_ERROR;
            } else {
                $submission->sending_status = Submission::STATUS_SUCCESS;
                if (!empty($submission->form->email_notification_addresses)) {
                    try {
                        $emailView = !empty($submission->form->email_notification_view)
                            ? $submission->form->email_notification_view
                            : '@app/widgets/form/views/email-template.php';
                        Yii::$app->mail->compose(
                            $emailView,
                            [
                                'form' => $submission->form,
                                'submission' => $submission,
                            ]
                        )->setTo(explode(',', $submission->form->email_notification_addresses))->setFrom(
                            Yii::$app->mail->getMailFrom()
                        )->setSubject($submission->form->name . ' #' . $submission->id)->send();
                    } catch (\Exception $e) {
                        $submission->sending_status = $errorStatus;
                    }
                }
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
        Submission::deleteAll(
            'UNIX_TIMESTAMP(`date_received`) < ' . $time->getTimestamp() . ' AND `is_deleted` = \'1\''
        );
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
