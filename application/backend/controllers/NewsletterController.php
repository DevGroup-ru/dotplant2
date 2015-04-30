<?php

namespace app\backend\controllers;

use app\backend\models\NewsletterConfig;
use app\modules\page\models\Page;
use app\models\SubscribeEmail;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class NewsletterController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['newsletter manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionConfig()
    {
        $model = new NewsletterConfig();

        if ($model->load(Yii::$app->request->get())) {
            $model->saveConfig();
        }

        return $this->render(
            'config',
            [
                'model' => $model
            ]
        );
    }

    public function actionEmailList()
    {
        $searchModel = new SubscribeEmail();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'emaillist',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel
            ]
        );
    }

    public function actionUpdate($id)
    {
        $pk = SubscribeEmail::primaryKey();
        $pk = $pk[0];
        $condition = [$pk => $id];

        $model = SubscribeEmail::findOne($condition);

        if (null === $model) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Entries with this ID does not exist'));
        } else {
            if ($model->load(Yii::$app->request->get())) {
                $model->save();
            }
        }

        return $this->render(
            'update',
            [
                'model' => $model
            ]
        );
    }

    public function actionDelete($id)
    {
        $pk = SubscribeEmail::primaryKey();
        $pk = $pk[0];
        $condition = [$pk => $id];

        $model = SubscribeEmail::findOne($condition);

        if (null !== $model) {
            SubscribeEmail::deleteAll($condition);
        }

        return Yii::$app->response->redirect('email-list', 301);
    }

    public function actionNewslist()
    {
        $searchModel = new Page();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'newslist',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel
            ]
        );
    }

    public function actionSendnow($id)
    {
        $model = Page::findById($id);
        if (null === $model) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Document with this ID does not exist'));
            return $this->render('@app/views/notifications/newsletter/sendnow');
        }

        $emailsOk = [];
        $emailsBad = [];
        $activeSubscribes = (new SubscribeEmail())->getActiveSubscribes();
        foreach ($activeSubscribes as $sEmail) {
            $status = Yii::$app->mail->compose(
                '@app/views/notifications/newsletter/sendnow-notify.php',
                [
                    'user' => $sEmail->name,
                    'page' => $model
                ]
            )->setTo($sEmail->email)
                ->setFrom(Yii::$app->mail->transport->getUserName())
                ->setSubject(Yii::t('app', 'New notify'))
                ->send();

            if ($status) {
                array_push($emailsOk, $sEmail);
            } else {
                array_push($emailsBad, $sEmail);
            }
        }

        $params = [
            'emailsBad' => $emailsBad,
            'emailsOk' => $emailsOk
        ];

        if (count($emailsBad) > 0) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'There are addresses that email could not be sent'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Subscribe completed'));
        }

        return $this->render('sendnow', $params);
    }
}
