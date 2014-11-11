<?php

namespace app\backend\controllers;

use app\reviews\models\Review;
use yii\filters\AccessControl;
use yii\web\Controller;

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

    public function actionIndex()
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

    public function actionUpdateStatus()
    {
        $id = \Yii::$app->request->post('editableKey');
        $index = \Yii::$app->request->post('editableIndex');
        $reviews = \Yii::$app->request->post('Review', []);
        if ($id !== null && $index !== null) {
            $review = Review::findOne($id);
            $review->status = $reviews[$index]['status'];
            return $review->update();
        } else {
            return false;
        }
    }
}
