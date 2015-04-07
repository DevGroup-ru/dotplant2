<?php

namespace app\backend\controllers;

use app\models\SpamChecker;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
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

        $searchModel = new SpamChecker;
        $params = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);

        $post = Yii::$app->request->post();
        if (ArrayHelper::keyExists($searchModel->formName(), $post)) {
            SpamChecker::setEnabledApiId(
                ArrayHelper::getValue($post, $searchModel->formName() . '.enabledApiId')
            );
        }

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionEdit($id = null)
    {
        if ($id === null) {
            $model = new SpamChecker;
        } else {
            $model = SpamChecker::findOne($id);
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
        $model = SpamChecker::findOne($id);
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
            SpamChecker::deleteAll(['in', 'id', $items]);
        }
        $this->render(['index']);
    }

}
