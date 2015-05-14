<?php

namespace app\modules\shop\controllers;


use app\modules\shop\models\AbstractDiscountType;
use app\modules\shop\models\Discount;
use app\modules\shop\models\DiscountType;
use yii\filters\AccessControl;
use app\backend\components\BackendController;
use Yii;
use yii\helpers\Url;

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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {

                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/shop/discount-backend/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/shop/discount-backend/edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/shop/discount-backend/edit',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            }


        }

        if (Yii::$app->request->isPost && !$model->isNewRecord) {
            foreach($model->getTypeObjects() as $object)
            {
                $object->discount_id = $model->id;

                if ($object->load(Yii::$app->request->post()) && $object->validate()) {
                    $object->save();
                }
            }
        }


        return $this->render('form', [
            'model' => $model,
        ]);
    }


    public function actionDeleteFilters($typeId, $id, $returnUrl)
    {
        $discountType = DiscountType::findOne($typeId);
        $class = new $discountType->class;
        /** @var $class AbstractDiscountType * */
        $class::deleteAll(['id' => $id]);
        $this->redirect($returnUrl);
    }


}