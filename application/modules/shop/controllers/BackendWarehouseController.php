<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\components\SearchModel;
use app\modules\shop\models\WarehouseEmail;
use app\modules\shop\models\WarehouseOpeninghours;
use app\modules\shop\models\WarehousePhone;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use app\backend\actions\DeleteOne;
use app\backend\actions\MultipleDelete;
use app\backend\actions\UpdateEditable;
use app\modules\shop\models\Warehouse;
use app\modules\shop\models\WarehouseProduct;
use yii\helpers\Url;
use Yii;
use yii\data\ActiveDataProvider;
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
        ;
        $searchModel = new SearchModel(
            [
                'model' => Warehouse::className(),
                'partialMatchAttributes' => ['name'],
                'scenario' => 'default',
            ]
        );
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
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


        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get(
                    'returnUrl',
                    ['/shop/backend-warehouse/index']
                );
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/shop/backend-warehouse/edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/shop/backend-warehouse/edit',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            }


        }

        $wareHouseOpeningHours = WarehouseOpeninghours::find()->where(['warehouse_id' => $model->id])->one();
        if ($wareHouseOpeningHours === null) {
            $wareHouseOpeningHours = new WarehouseOpeninghours();
        }
        $wareHouseOpeningHours->loadDefaultValues();
        if (Yii::$app->request->post('WarehouseOpeninghours') && !$model->isNewRecord) {
            $wareHouseOpeningHours->load(Yii::$app->request->post());
            $wareHouseOpeningHours->warehouse_id = $model->id;
            if ($wareHouseOpeningHours->save()) {
                $this->refresh();
            }
        }

        $warehousePhone = new WarehousePhone();

        if (Yii::$app->request->post('WarehousePhone') && !$model->isNewRecord) {
            $warehousePhone->loadDefaultValues();
            $warehousePhone->load(Yii::$app->request->post());
            $warehousePhone->warehouse_id = $model->id;
            if ($warehousePhone->save()) {
                $this->refresh();
            }

        }
        $warehousePhoneProvider = new ActiveDataProvider([
            'query' => $warehousePhone::find()->where(['warehouse_id' => $model->id]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);


        $warehouseEmail = new WarehouseEmail();
        if (Yii::$app->request->post('WarehouseEmail') && !$model->isNewRecord) {
            $warehouseEmail->loadDefaultValues();
            $warehouseEmail->load(Yii::$app->request->post());
            $warehouseEmail->warehouse_id = $model->id;
            if ($warehouseEmail->save()) {
                $this->refresh();
            }

        }
        $warehouseEmailProvider = new ActiveDataProvider([
            'query' => $warehouseEmail::find()->where(['warehouse_id' => $model->id]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);


        return $this->render(
            'form',
            [
                'model' => $model,
                'wareHouseOpeningHours' => $wareHouseOpeningHours,
                'warehousePhone' => $warehousePhone,
                'warehousePhoneProvider' => $warehousePhoneProvider,
                'warehouseEmail' => $warehouseEmail,
                'warehouseEmailProvider' => $warehouseEmailProvider
            ]
        );
    }


    public function actionDelete($id)
    {
        if (!$model = Warehouse::findOne($id)) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object not removed'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                '/shop/backend-warehouse/index'
            )
        );
    }

    public function actionEditPhone($id, $returnUrl)
    {
        $warehousePhone = WarehousePhone::findOne($id);

        if (Yii::$app->request->post('WarehousePhone')) {
            $warehousePhone->loadDefaultValues();
            $warehousePhone->load(Yii::$app->request->post());
            if ($warehousePhone->save()) {
                $this->redirect($returnUrl);
            }
        }



        return $this->render('form_edit_phone', ['warehousePhone' => $warehousePhone]);
    }


    public function actionEditEmail($id, $returnUrl)
    {
        $warehouseEmail = WarehouseEmail::findOne($id);

        if (Yii::$app->request->post('WarehouseEmail')) {
            $warehouseEmail->loadDefaultValues();
            $warehouseEmail->load(Yii::$app->request->post());
            if ($warehouseEmail->save()) {
                $this->redirect($returnUrl);
            }
        }

        return $this->render('form_edit_email', ['warehouseEmail' => $warehouseEmail]);
    }

    public function actionDeletePhone($id)
    {
        if (!$model = WarehousePhone::findOne($id)) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object not removed'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                '/shop/backend-warehouse/index'
            )
        );
    }

    public function actionDeleteEmail($id)
    {
        if (!$model = WarehouseEmail::findOne($id)) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object not removed'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                '/shop/backend-warehouse/index'
            )
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
            TagDependency::invalidate(Yii::$app->cache,
                ActiveRecordHelper::getObjectTag(\app\modules\shop\models\Product::className(), $model->product_id));
            return $model->save();


        } else {
            throw new HttpException(400);
        }
    }

}
