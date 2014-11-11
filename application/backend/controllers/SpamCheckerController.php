<?php

namespace app\backend\controllers;

use app\models\Config;
use app\models\SpamChecker;
use yii\filters\AccessControl;
use yii\web\Controller;

class SpamCheckerController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['setting manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new SpamChecker();

        $post = \Yii::$app->request->post();
        $config = new Config();

        if (isset($post[$model->formName()])) {
            $model->load($post);
            $this->saveConfig($model);
        } else {
            $spamCheckerConfig = $config->findOne(['key' => 'spamCheckerConfig']);
            if ($spamCheckerConfig === null) {
                $config->key = 'spamCheckerConfig';
                $config->name = 'Spam Checker Config';
                $config->value = '';
                $config->preload = 0;

                if ($config->save()) {
                    $this->saveConfig(null, $config->id, true);
                } else {
                    // error
                    // @TODO сделать обработку ошибок
                }
            } else {
                $model->yandexApiKey = $config->getValue("spamCheckerConfig.apikeys.yandexAPIKey");
                $model->akismetApiKey = $config->getValue("spamCheckerConfig.apikeys.akismetAPIKey");
                $model->enabledApiKey = $config->getValue("spamCheckerConfig.enabledApiKey");
                $model->configFieldsParentId = $config->getValue("spamCheckerConfig.configFieldsParentId");
            }
        }

        return $this->render(
            'index',
            [
                'model' => $model
            ]
        );
    }

    private function saveConfig($model, $parentID = 0, $newRecord = false)
    {
        $configs = [
            [
                'key' => 'yandexAPIKey',
                'name' => 'Yandex API key',
                'value' => $model->yandexApiKey
            ],
            [
                'key' => 'akismetAPIKey',
                'name' => 'Akismet API key',
                'value' => $model->akismetApiKey
            ],
            [
                'key' => 'configFieldsParentId',
                'name' => 'Config Fields Parent Id',
                'value' => $model->configFieldsParentId
            ],
            [
                'key' => 'enabledApiKey',
                'name' => 'Enabled API key',
                'value' => $model->enabledApiKey
            ]
        ];

        if ($newRecord) {
            foreach ($configs as $conf) {
                $config = new Config();
                $config->parent_id = $parentID;
                $config->key = $conf['key'];
                $config->name = $conf['name'];
                $config->value = $conf['value'];

                $config->save();
            }
        } else {
            foreach ($configs as $conf) {
                $config = (new Config())->findOne(['key' => $conf['key']]);
                $config->value = $conf['value'];
                $config->save();
            }
        }
    }
}
