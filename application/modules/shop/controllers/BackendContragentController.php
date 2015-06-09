<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\components\SearchModel;
use app\modules\shop\models\Contragent;
use app\modules\shop\models\Customer;
use app\modules\shop\models\DeliveryInformation;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class BackendContragentController extends BackendController
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
                        'roles' => ['order manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionIndex()
    {
        $searchModelConfig = [
            'model' => Contragent::className(),
            'additionalConditions' => [],
        ];

        /** @var SearchModel $searchModel */
        $searchModel = new SearchModel($searchModelConfig);
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param null $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionEdit($id = null)
    {
        if (null === $id) {
            throw new NotFoundHttpException();
        }

        /** @var Contragent $model */
        if (null === $model = Contragent::findOne(['id' => $id])) {
            throw new NotFoundHttpException();
        }

        if (true === \Yii::$app->request->isPost && $model->load(\Yii::$app->request->post())) {
            if ($model->saveModelWithProperties(\Yii::$app->request->post())) {
                return $this->refresh();
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param null $customer
     * @return string|\yii\web\Response
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionCreate($customer = null)
    {
        $customer = intval($customer) > 0 ? Customer::findOne(['id' => intval($customer)]) : null;
        $contragent = Contragent::createEmptyContragent(null === $customer ? Customer::createEmptyCustomer() : $customer);

        if (true === \Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
            if ($contragent->load($data) && $contragent->save()) {
                if (!empty($contragent->getPropertyGroup())) {
                    $contragent->getPropertyGroup()->appendToObjectModel($contragent);
                    $data[$contragent->getAbstractModel()->formName()] = isset($data['ContragentNew']) ? $data['ContragentNew'] : [];
                }
                $contragent->saveModelWithProperties($data);
                $contragent->refresh();

                $deliveryInformation = DeliveryInformation::createNewDeliveryInformation($contragent, false);

                return $this->redirect(Url::toRoute(['edit', 'id' => $contragent->id]));
            }
        }

        return $this->render('create', [
            'model' => $contragent
        ]);
    }

    /**
     * @return array
     */
    public function actionAjaxCustomer()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'more' => false,
            'results' => []
        ];
        $search = \Yii::$app->request->get('search', []);
        if (!empty($search['term'])) {
            $query = Customer::find()
                ->select('id, first_name, middle_name, last_name, email, phone')
                ->where(['like', 'first_name', trim($search['term'])])
                ->orWhere(['like', 'middle_name', trim($search['term'])])
                ->orWhere(['like', 'last_name', trim($search['term'])])
                ->orWhere(['like', 'email', trim($search['term'])])
                ->orWhere(['like', 'phone', trim($search['term'])])
                ->asArray();

            $result['results'] = array_values($query->all());
        }

        return $result;
    }
}