<?php

namespace app\modules\shop\controllers;


use app\modules\shop\models\Discount;
use yii\filters\AccessControl;
use app\backend\components\BackendController;
use Yii;

class DiscountBackendController extends BackendController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['product manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Discount();

        $params = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);

        $model = null;

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => $model,
            ]
        );
    }


    /**
     * Updates an existing Discount model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEdit($id = false)
    {
        $model = new Discount();

        if ($id !== false) {
            $model = Discount::findOne($id);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }


}