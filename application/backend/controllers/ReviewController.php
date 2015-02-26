<?php

namespace app\backend\controllers;

use app\reviews\models\Review;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ReviewController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['review manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionProducts()
    {
        $searchModel = new Review(['scenario' => 'search']);
        $dataProvider = $searchModel->productSearch($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }
    public function actionPages()
    {
        $searchModel = new Review(['scenario' => 'search']);
        $dataProvider = $searchModel->pageSearch($_GET);

        return $this->render(
            'pages',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionUpdateStatus()
    {
        $id = \Yii::$app->request->post('editableKey');
        $index = \Yii::$app->request->post('editableIndex');
        $reviews = \Yii::$app->request->post('Review', []);
        if ($id !== null && $index !== null) {
            $review = $this->loadModel($id);
            $review->status = $reviews[$index]['status'];
            return $review->update();
        } else {
            return false;
        }
    }

    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        $model->delete();
        return $this->redirect(['index']);
    }

    protected function loadModel($id)
    {
        $model = Review::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
