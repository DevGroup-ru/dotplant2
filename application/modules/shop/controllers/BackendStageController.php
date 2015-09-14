<?php

namespace app\modules\shop\controllers;

use app\modules\core\models\Events;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use app\traits\LoadModel;
use yii;
use yii\filters\AccessControl;
use yii\helpers\Url;

class BackendStageController extends \app\backend\components\BackendController
{
    use LoadModel;

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
        return $this->render('stage-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLeafIndex()
    {
        $searchModel = new OrderStageLeaf();
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('leaf-index', [
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

        $events = array_reduce(Events::find()->all(),
            function ($result, $item)
            {
                /** @var Events $item */
                $result[$item->event_name] = $item->event_name;
                return $result;
            }, ['' => '']);

        if (empty($model)) {
            $model = new OrderStage();
            $model->loadDefaultValues();
        }

        return $this->render('stage-edit', [
            'model' => $model,
            'events' => $events,
        ]);
    }

    public function actionLeafEdit($id = null)
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

        if (empty($model)) {
            $model = new OrderStageLeaf();
            $model->loadDefaultValues();
        }

        $stages = array_reduce(OrderStage::find()->all(),
            function ($result, $item)
            {
                $result[$item->id] = $item->name;
                return $result;
            }, []);

        $events = array_reduce(Events::find()->all(),
            function ($result, $item)
            {
                /** @var Events $item */
                $result[$item->event_name] = $item->event_name;
                return $result;
            }, ['' => '']);

        return $this->render('leaf-edit', [
            'model' => $model,
            'stages' => $stages,
            'events' => $events,
        ]);
    }

    public function actionLeafDelete($id)
    {
        if (Yii::$app->request->isPost === false) {
            throw new yii\web\BadRequestHttpException;
        }
        if ($id === null) {
            throw new yii\web\BadRequestHttpException;
        }
        /** @var OrderStageLeaf $model */
        $model = $this->loadModel(OrderStageLeaf::className(), $id);
        $model->delete();

        Yii::$app->session->setFlash('info', Yii::t('app', 'Leaf successfully deleted. Check your stages and leafs for correct workflow!'));

        return $this->redirect('leaf-index');
    }

    public function actionStageDelete($id)
    {
        if (Yii::$app->request->isPost === false) {
            throw new yii\web\BadRequestHttpException;
        }
        if ($id === null) {
            throw new yii\web\BadRequestHttpException;
        }
        /** @var OrderStage $model */
        $model = $this->loadModel(OrderStage::className(), $id);
        $model->delete();

        Yii::$app->session->setFlash('info', Yii::t('app', 'Stage successfully deleted. Check your stages and leafs for correct workflow!'));

        return $this->redirect('stage-index');
    }

    public function actionRenderGraph()
    {
        return $this->render('render-graph');
    }
}
?>