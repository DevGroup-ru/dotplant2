<?php

namespace app\commands;

use app\models\Object;
use app\models\ObjectStaticValues;
use app\models\Submission;
use app\modules\review\models\Review;
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
            echo "Sending {$submission->id}\n";
            if ($submission->form === null) {
                $submission->processed = Submission::STATUS_FATAL_ERROR;
            } else {
                $submission->sending_status = Submission::STATUS_SUCCESS;
                if (!empty($submission->form->email_notification_addresses)) {
                    try {
                        $emailView = !empty($submission->form->email_notification_view)
                            ? $submission->form->email_notification_view
                            : '@app/widgets/form/views/email-template.php';

                        /** @var \app\modules\core\components\MailComponent $mail */
                        $mail = Yii::$app->mail;
                        $msg = $mail->compose(
                            $emailView,
                            [
                                'form' => $submission->form,
                                'submission' => $submission,
                            ]
                        )->setTo(explode(',', $submission->form->email_notification_addresses))->setFrom(
                            Yii::$app->mail->getMailFrom()
                        )->setSubject($submission->form->name . ' #' . $submission->id);

                        if (Yii::$app->getModule('core')->attachFilePropertiesToFormEmail === true) {
                            $properties = $submission->abstractModel->getPropertiesModels();
                            $basePath = Yii::getAlias(Yii::$app->getModule('core')->visitorsFileUploadPath) . '/';
                            foreach ($properties as $property) {
                                /** @var \app\models\Property $property */
                                if (stripos($property->getHandler()->handler_class_name, 'FileInput') !== false) {
                                    $filename = $basePath . $submission->property($property->key);
                                    $msg = $msg->attach($filename);
                                }
                            }
                        }

                        $msg->send();

                    } catch (\Exception $e) {
                        echo "Exception\n";
                        $submission->sending_status = $errorStatus;
                        $submission->internal_comment = $e->getMessage() ."\n\n".$e->getFile().":".$e->getLine();
                        echo $e->getMessage() ."\n\n".$e->getFile().":".$e->getLine();
                    }
                } else {
                    echo "No email\n";
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
        /** @var Object $object */
        $object = Object::getForClass(Submission::className());
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
