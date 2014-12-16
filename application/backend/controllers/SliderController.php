<?php

namespace app\backend\controllers;

use app\slider\BaseSliderEditModel;
use Yii;
use app\models\Slider;
use app\components\SearchModel;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * SliderController implements the CRUD actions for Slider model.
 */
class SliderController extends Controller
{
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
                        'roles' => ['content manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Slider models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchModel(
            [
                'model' => Slider::className(),
                'partialMatchAttributes' => ['name'],
                'scenario' => 'default',
            ]
        );
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Updates an existing Slider model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id = null)
    {
        $abstractModel = new BaseSliderEditModel();

        if (is_null($id)) {
            $model = new Slider;
            $model->loadDefaultValues();
        } else {
            $model = $this->findModel($id);
            if ($model->handler() !== null) {
                $abstractModel = Yii::createObject(['class'=>$model->handler()->edit_model]);
                if (!empty($model->params)) {
                    $abstractModel->unserialize($model->params);
                }
            }
        }

        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {
            if ($model->handler() !== null) {
                $abstractModel = Yii::createObject(['class'=>$model->handler()->edit_model]);
                if (!empty($model->params)) {
                    $abstractModel->unserialize($model->params);
                }
            }
            $abstractModel->load($post);
            if ($abstractModel->validate()) {
                $model->params = $abstractModel->serialize();
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                    return $this->redirect(['update', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }
        return $this->render(
            'update',
            [
                'model' => $model,
                'abstractModel' => $abstractModel,
            ]
        );
    }

    /**
     * Deletes an existing Slider model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Slider model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Slider the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Slider::findById($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
