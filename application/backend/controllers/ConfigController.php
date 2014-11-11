<?php

namespace app\backend\controllers;

use Yii;
use app\backend\actions\JSTreeGetTrees;
use app\components\SearchModel;
use app\models\Config;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ConfigController implements the CRUD actions for Config model.
 */
class ConfigController extends Controller
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

    public function actions()
    {
        return [
            'getTree' => [
                'class' => JSTreeGetTrees::className(),
                'modelName' => Config::className(),
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
                'show_deleted' => null
            ],
        ];
    }

    /**
     * Lists all Config models.
     * @param int $parent_id
     * @return string
     */
    public function actionIndex($parent_id = 0)
    {
        $searchModel = new SearchModel(
            [
                'model' => Config::className(),
                'partialMatchAttributes' => ['name', 'key', 'value'],
            ]
        );
        $searchModel->parent_id = $parent_id;
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
     * Updates an existing Config model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null|string $id
     * @param int $parent_id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id = null, $parent_id = 0)
    {
        if ($id === null) {
            $model = new Config;
            $model->parent_id = $parent_id;
        } else {
            $model = $this->findModel($id);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                return $this->redirect(['update', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }
        return $this->render(
            'update',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Deletes an existing Config model.
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
     * Finds the Config model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Config the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Config::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
