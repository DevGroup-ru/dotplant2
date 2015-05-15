<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use yii;
use yii\filters\AccessControl;
use yii\helpers\Url;

class BackendController extends \app\backend\components\BackendController
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
        return $this->render('index');
    }

    public function actionStageIndex()
    {
        $searchModel = new OrderStage();
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('stage/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionStageLeafIndex()
    {
        $searchModel = new OrderStageLeaf();
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('stage/leaf-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionStageEdit($id = null)
    {
        $model = null;
        if (null !== $id) {
            $model = OrderStage::findOne(['id' => $id]);
        }

        if (Yii::$app->request->isPost) {
            if (empty($model)) {
                $model = new OrderStage();
                $model->loadDefaultValues();
            }

            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate() && $model->save()) {
                    return $this->redirect(Url::to(['', 'id' => $model->id]));
                } else {
                    Yii::$app->session->setFlash('error', 'Error saving data.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Error loading data.');
            }
        }

        if (empty($model)) {
            $model = new OrderStage();
            $model->loadDefaultValues();
            return $this->render('stage/create', ['model' => $model]);
        }

        return $this->render('stage/edit', ['model' => $model]);
    }

    public function actionStageLeafEdit($id = null)
    {
        $model = null;
        if (null !== $id) {
            $model = OrderStageLeaf::findOne(['id' => $id]);
        }

        if (Yii::$app->request->isPost) {
            if (empty($model)) {
                $model = new OrderStageLeaf();
                $model->loadDefaultValues();
            }

            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate() && $model->save()) {
                    return $this->redirect(Url::to(['', 'id' => $model->id]));
                } else {
                    Yii::$app->session->setFlash('error', 'Error saving data.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Error loading data.');
            }
        }

        $stages = array_reduce(OrderStage::find()->all(),
            function ($result, $item)
            {
                $result[$item->id] = $item->name;
                return $result;
            }, []);

        if (empty($model)) {
            $model = new OrderStageLeaf();
            $model->loadDefaultValues();
            return $this->render('stage/leaf-create', [
                'model' => $model,
                'stages' => $stages,
            ]);
        }

        return $this->render('stage/leaf-edit', [
            'model' => $model,
            'stages' => $stages,
        ]);
    }
}
?>