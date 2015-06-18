<?php

namespace app\actions;

use app\behaviors\spamchecker\SpamCheckerBehavior;
use app\models\Form;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\Property;
use app\models\SpamChecker;
use app\models\Submission;
use app\properties\AbstractModel;
use app\properties\HasProperties;
use kartik\widgets\ActiveForm;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class SubmitFormAction extends Action
{
    /**
     * @param int $id
     * @return int|mixed
     * @throws NotFoundHttpException
     */
    public function run($id)
    {
        /** @var Form|HasProperties $form */
        if (null === $form = Form::findById($id)) {
            throw new NotFoundHttpException();
        }

        $post = \Yii::$app->request->post();
        $form->abstractModel->setAttrubutesValues($post);
        /** @var AbstractModel|SpamCheckerBehavior $model */
        $model = $form->getAbstractModel();

        if (\Yii::$app->request->isAjax && isset($post['ajax'])) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        /** @var \app\models\Object $object */
        $object = Object::getForClass(Form::className());
        $propGroups = ObjectPropertyGroup::find()->where(
            [
                'and',
                'object_id = :object',
                'object_model_id = :id'
            ],
            [
                ':object' => $object->id,
                ':id' => $id
            ]
        )->asArray()->all();
        $propIds = ArrayHelper::getColumn($propGroups, 'property_group_id');

        // Spam checking
        $activeSpamChecker = SpamChecker::getActive();
        $data = [];
        $spamResult = [];
        if ($activeSpamChecker !== null && !empty($activeSpamChecker->api_key)) {
            $data[$activeSpamChecker->name]['class'] = $activeSpamChecker->behavior;
            $data[$activeSpamChecker->name]['value']['key'] = $activeSpamChecker->api_key;
            $properties = Property::getForGroupId($propIds[0]);
            foreach ($properties as $prop) {
                if (!isset($activeSpamChecker->{$prop->interpret_as})
                    || empty($activeSpamChecker->{$prop->interpret_as})
                ) {
                    continue;
                }
                $data[$activeSpamChecker->name]['value'][$activeSpamChecker->{$prop->interpret_as}] =
                    is_array($post[$form->abstractModel->formName()][$prop->key])
                        ? implode(' ', $post[$form->abstractModel->formName()][$prop->key])
                        : $post[$form->abstractModel->formName()][$prop->key];
            }
            $model->attachBehavior(
                'spamChecker',
                [
                    'class' => SpamCheckerBehavior::className(),
                    'data' => $data,
                ]
            );
            $spamResult = $model->check();
        }
        $haveSpam = false;
        if (is_array($spamResult) === true) {
            foreach ($spamResult as $result) {
                if (ArrayHelper::getValue($result, 'ok', false) === true) {
                    $haveSpam = $haveSpam || ArrayHelper::getValue($result, 'is_spam', false);
                }
            }
        }

        $date = new \DateTime();
        /** @var Submission|HasProperties $submission */
        $submission = new Submission(
            [
                'form_id' => $form->id,
                'date_received' => $date->format('Y-m-d H:i:s'),
                'ip' => Yii::$app->request->userIP,
                'user_agent' => Yii::$app->request->userAgent,
                'spam' => (int) $haveSpam,
            ]
        );
        if (false === Yii::$app->user->isGuest) {
            $submission->processed_by_user_id = Yii::$app->user->identity->getId();
        }
        if (!($form->abstractModel->validate() && $submission->save())) {
            return "0";
        }
        if (isset($post[$form->abstractModel->formName()])) {
            $data = [
                'AddPropetryGroup' => [
                    $submission->formName() => array_keys($form->getPropertyGroups()),
                ],
                $submission->abstractModel->formName() => $post[$form->abstractModel->formName()],
            ];
            if (isset($_FILES[$form->abstractModel->formName()])) {
                $_FILES[$submission->abstractModel->formName()] = $_FILES[$form->abstractModel->formName()];
            }
            $submission->saveProperties($data);
        }
        if ($haveSpam === false) {
            if (!empty($form->email_notification_addresses)) {
                try {
                    $emailView = !empty($form->email_notification_view)
                        ? $form->email_notification_view
                        :'@app/widgets/form/views/email-template.php';
                    Yii::$app->mail->compose(
                        $emailView,
                        [
                            'form' => $form,
                            'submission' => $submission,
                        ]
                    )->setTo(explode(',', $form->email_notification_addresses))->setFrom(
                        Yii::$app->mail->getMailFrom()
                    )->setSubject($form->name . ' #' . $submission->id)->send();
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }
        return $submission->id;
    }
}
