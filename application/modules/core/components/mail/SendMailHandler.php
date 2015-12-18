<?php

namespace app\modules\core\components\mail;


use app\models\Submission;
use yii\base\Event;
use Yii;


class SendMailHandler
{

    public static function submissionsSendMail(Event $event)
    {

        if (!empty($event->sender->form->email_notification_addresses)) {
            try {
                $emailView = !empty($event->sender->form->email_notification_view)
                    ? $event->sender->form->email_notification_view
                    : '@app/widgets/form/views/email-template.php';

                /** @var \app\modules\core\components\MailComponent $mail */
                $mail = \Yii::$app->mail;
                $msg = $mail->compose(
                    $emailView,
                    [
                        'form' => $event->sender->form,
                        'submission' => $event->sender,
                    ]
                )->setTo(explode(',', $event->sender->form->email_notification_addresses))->setFrom(
                    \Yii::$app->mail->getMailFrom()
                )->setSubject($event->sender->getSubject());

                if (\Yii::$app->getModule('core')->attachFilePropertiesToFormEmail === true) {
                    $properties = $event->sender->abstractModel->getPropertiesModels();
                    $basePath = Yii::getAlias(Yii::$app->getModule('core')->visitorsFileUploadPath) . '/';
                    foreach ($properties as $property) {
                        /** @var \app\models\Property $property */
                        if (stripos($property->getHandler()->handler_class_name, 'FileInput') !== false) {
                            $filename = $basePath . $event->sender->property($property->key);
                            $msg = $msg->attach($filename);
                        }
                    }
                }

                if ($msg->send()) {
                    $event->sender->sending_status = Submission::STATUS_SUCCESS;
                }

            } catch (\Exception $e) {
                echo "Exception\n";
                $event->sender->sending_status = Submission::STATUS_ERROR;
                $event->sender->internal_comment = $e->getMessage() . "\n\n" . $e->getFile() . ":" . $e->getLine();
                echo $e->getMessage() . "\n\n" . $e->getFile() . ":" . $e->getLine();
            }
        } else {
            echo "No email\n";
        }


    }

}