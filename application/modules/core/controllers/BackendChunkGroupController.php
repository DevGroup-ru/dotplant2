<?php

namespace app\modules\core\controllers;

use app\backend\components\BackendController;
use Yii;
use app\modules\core\models\ContentBlockGroup;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ContentBlockGroupController implements the CRUD actions for ContentBlockGroup model.
 */
class BackendChunkGroupController extends BackendController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ContentBlockGroup models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ContentBlockGroup::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ContentBlockGroup model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ContentBlockGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parent_id = 1, $returnUrl = null)
    {
        $model = new ContentBlockGroup();
        $model->loadDefaultValues();
        if (empty($model->parent_id)) {
            $model->parent_id = (int)$parent_id;
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($returnUrl) {
                return $this->redirect($returnUrl);
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ContentBlockGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $returnUrl = null)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($returnUrl) {
                return $this->redirect($returnUrl);
            }
            $this->refresh();
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ContentBlockGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id, $returnUrl = null)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->post());
        $model->delete();
        if ($returnUrl) {
            return $this->redirect($returnUrl);
        }
        return $this->redirect(['/core/backend-chunk/index']);
    }

    /**
     * Finds the ContentBlockGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ContentBlockGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ContentBlockGroup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
