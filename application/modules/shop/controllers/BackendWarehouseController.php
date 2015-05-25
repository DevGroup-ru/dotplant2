<?php

namespace app\backend\controllers;

use app\backend\components\BackendController;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use app\backend\actions\DeleteOne;
use app\backend\actions\MultipleDelete;
use app\backend\actions\UpdateEditable;
use app\modules\shop\models\Warehouse;
use app\modules\shop\models\WarehouseProduct;
use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\caching\TagDependency;

class BackendWarehouseController extends BackendController
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
                        'roles' => ['product manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'remove-all' => [
                'class' => MultipleDelete::className(),
                'modelName' => Warehouse::className(),
            ],
            'delete' => [
                'class' => DeleteOne::className(),
                'modelName' => Warehouse::className(),
            ],
            'update-editable' => [
                'class' => UpdateEditable::className(),
                'modelName' => Warehouse::className(),
                'allowedAttributes' => [
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Warehouse();
        $dataProvider = $searchModel->search($_GET);

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
        $model = new Warehouse;
        $model->loadDefaultValues();
        
        if ($id !== null) {
            $model = Warehouse::findOne($id);
        }
        


        $post = \Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {

            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                return $this->redirect(['edit', 'id' => $model->id]);
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }


        }

        return $this->render(
            'form',
            [
                'model' => $model,
            ]
        );
    }

    public function actionUpdateRemains()
    {
        $post = Yii::$app->request->post('remain', null);
        if (isset($post)) {

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $remainId = current(array_keys($post));
            /** @var WarehouseProduct $model */
            $model = WarehouseProduct::findOne($remainId);
            if ($model === null) {
                throw new NotFoundHttpException;
            }

            $model->setAttributes(current($post));
            TagDependency::invalidate(Yii::$app->cache, ActiveRecordHelper::getObjectTag(\app\modules\shop\models\Product::className(), $model->product_id));
            return $model->save();


        } else {
            throw new HttpException(400);
        }
    }

}
