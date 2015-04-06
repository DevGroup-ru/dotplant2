<?php

namespace app\backend\controllers;

use app\models\Config;
use app\models\SpamChecker;
use app\models\SpamCheckerBehavior;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
        // @TODO REWRITE !!!!!
        $model = new SpamChecker();

        $searchModel = new SpamCheckerBehavior;
        $params = \Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);

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
                'model' => $model,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionEdit($id = null)
    {
        if ($id === null) {
            $model = new SpamCheckerBehavior;
        } else {
            $model = SpamCheckerBehavior::findOne($id);
        }
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                'edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            [
                                'edit',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }
        return $this->render('spam-checker-form', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = SpamCheckerBehavior::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        if ($model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Object not removed'));
        }
        return $this->redirect(['index']);
    }

    public function actionDeleteAll()
    {
        $items = Yii::$app->request->post('items', []);
        if (empty($items) === false) {
            SpamCheckerBehavior::deleteAll(['in', 'id', $items]);
        }
        $this->render(['index']);
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
