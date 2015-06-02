<?php

namespace app\actions;

use app\behaviors\spamchecker\SpamCheckerBehavior;
use app\models\Config;
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
use yii\web\Response;
use yii\web\UploadedFile;

class SubmitFormAction extends Action
{
    public function run($id)
    {
        $post = \Yii::$app->request->post();
        /** @var Form|HasProperties $form */
        $form = Form::findOne($id);
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

        /** Проверка на спам */
        $activeSpamChecker = SpamChecker::getActive();
        $data = [];
        $spamResult = [];
        if ($activeSpamChecker !== null) {
            $apiKey = ArrayHelper::getValue($activeSpamChecker, 'api_key', '');
            if ($apiKey !== '') {
                $data[$activeSpamChecker->name]['class'] = $activeSpamChecker->behavior;
                $data[$activeSpamChecker->name]['value']['key'] = $activeSpamChecker->api_key;
                /** Интерпретации полей фомы */
                $properties = Property::getForGroupId($propIds[0]);
                foreach ($properties as $prop) {
                    if ($prop->interpret_as == 0) {
                        continue;
                    }
                    $interpreted = Config::findOne($prop->interpret_as);
                    if ($interpreted->key == 'notinterpret') {
                        continue;
                    }
                    $value = $post[$form->abstractModel->formName()][$prop->key];
                    $interpretedKey = ArrayHelper::getValue($activeSpamChecker, $interpreted->key, '');
                    if ($interpretedKey !== '') {
                        $data[$activeSpamChecker->name]['value'][$interpretedKey] = $value;
                    }
                }
                $model->attachBehavior(
                    'spamChecker',
                    [
                        'class' => SpamCheckerBehavior::className(),
                        'data' => $data
                    ]
                );
                $spamResult = $model->check();
            }
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
                'spam' => Yii::$app->formatter->asBoolean($haveSpam),
            ]
        );
        if (false === Yii::$app->user->isGuest) {
            $submission->processed_by_user_id = Yii::$app->user->identity->getId();
        }
        if (!($form->abstractModel->validate() && $submission->save())) {
            return "0";
        }
        if (isset($post[$form->abstractModel->formName()])) {
            foreach ($post[$form->abstractModel->formName()] as $key => &$value) {
                if ($file = UploadedFile::getInstance($model, $key)) {
                    $folder = Yii::$app->getModule('core')->fileUploadPath;
                    $fullPath = "@webroot/{$folder}";
                    if (!file_exists(\Yii::getAlias($fullPath))) {
                        mkdir(\Yii::getAlias($fullPath), 0755, true);
                    }
                    $value = '/' . $folder . $file->baseName . '.' . $file->extension;
                    $file->saveAs($folder . $file->baseName . '.' . $file->extension);
                }
            }
            $data = [
                'AddPropetryGroup' => [
                    $submission->formName() => array_keys($form->getPropertyGroups()),
                ],
                $submission->abstractModel->formName() => $post[$form->abstractModel->formName()],
            ];
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
                        Yii::$app->mail->transport->getUsername()
                    )->setSubject($form->name . ' #' . $submission->id)->send();
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }
        return $submission->id;
    }
}
