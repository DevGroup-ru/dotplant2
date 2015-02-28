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
use yii;
use yii\base\Action;

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
            \Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        /** @var \app\models\Object $object */
        // @todo rewrite code below
        $object = Object::getForClass(Form::className());
        $propIds = (new yii\db\Query())->select('property_group_id')
            ->from([ObjectPropertyGroup::tableName()])
            ->where(
                [
                    'and',
                    'object_id = :object',
                    'object_model_id = :id'
                ],
                [
                    ':object' => $object->id,
                    ':id' => $id
                ]
            )->column();
        /** Проверка на спам */
        /** Получение API ключей */
        $data = [
            'yandex' => [ 'key' => Config::getValue('spamCheckerConfig.apikeys.yandexAPIKey') ],
            'akismet' => ['key' => Config::getValue('spamCheckerConfig.apikeys.akismetAPIKey')]
        ];
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
            /** Названия свойств фиксированные, хранятся в модели Config */
            switch($interpreted->key) {
                case "name":
                    $data['yandex']['realname'] = $value;
                    $data['akismet']['comment_author'] = $value;
                    break;
                case "content":
                    $data['yandex']['body-plain'] = $value;
                    $data['akismet']['comment_content'] = $value;
                    break;
            }
        }
        $enabledApiKey = (new SpamChecker())->getEnabledApiKeyPath();
        if ($enabledApiKey == 'spamCheckerConfig.apikeys.yandexAPIKey') {
            unset($data['akismet']);
        } else {
            unset($data['yandex']);
        }
        /** Если не указан api key, то нет смысла  запусать чекер */
        if (empty($data['yandex']['key'])) {
            unset($data['yandex']);
        }
        if (empty($data['akismet']['key'])) {
            unset($data['akismet']);
        }
        $model->attachBehavior(
            'spamChecker',
            [
                'class' => SpamCheckerBehavior::className(),
                'data' => $data
            ]
        );
        $spamResult = $model->check();
        $haveSpam = "not defined";
        if (is_array($spamResult)) {
            if (isset($spamResult['yandex']) && $spamResult['yandex']['ok'] == 1) {
                $haveSpam = $spamResult['yandex']['is_spam'];
            }
            if (isset($spamResult['akismet']) && $spamResult['akismet']['ok'] == 1) {
                if ($haveSpam != 'yes') {
                    $haveSpam = $spamResult['akismet']['is_spam'];
                }
            }
        }
        // @todo rewrite code above
        /** @var Submission|HasProperties $submission */
        $submission = new Submission(
            [
                'form_id' => $form->id,
                'ip' => Yii::$app->request->userIP,
                'user_agent' => Yii::$app->request->userAgent,
                'spam' => $haveSpam,
            ]
        );
        if (!($form->abstractModel->validate()  && $submission->save())) {
            return "0";
        }
        foreach($post[$form->abstractModel->formName()] as $key => &$value){
            if($file = yii\web\UploadedFile::getInstance($model, $key)){
                $folder = Config::getValue('core.fileUploadPath', 'upload/user-uploads/');
                $fullPath = "@webroot/{$folder}";
                if(!file_exists(\Yii::getAlias($fullPath))){
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
        if ($haveSpam == 'no' || $haveSpam == "not defined") {
            if (!empty($form->email_notification_addresses)) {
                try {
                    $emailView = !empty($form->email_notification_view)
                        ? $form->email_notification_view
                        : '@app/widgets/form/views/email-template.php';
                    Yii::$app->mail
                        ->compose(
                            $emailView,
                            [
                                'form' => $form,
                                'submission' => $submission,
                            ]
                        )
                        ->setTo(explode(',', $form->email_notification_addresses))
                        ->setFrom(Yii::$app->mail->transport->getUsername())
                        ->setSubject($form->name . ' #' . $submission->id)
                        ->send();
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }
        return "1";
    }
}
