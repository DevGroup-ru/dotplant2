<?php

namespace app\backend\controllers;

use app\backend\traits\BackendRedirect;
use app\models\SpamChecker;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SpamCheckerController extends Controller
{
    use BackendRedirect;
    /**
     * @inheritdoc
     */
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
                return $this->redirectUser($model->id);
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
