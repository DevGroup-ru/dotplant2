<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\components\SearchModel;
use app\modules\shop\models\Customer;
use app\modules\user\models\User;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class BackendCustomerController extends BackendController
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
            'model' => Customer::className(),
            'partialMatchAttributes' => ['first_name', 'middle_name', 'last_name'],
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
     * @param int|string|null $id
     * @return \yii\web\Response|string
     * @throws NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionEdit($id = null)
    {
        if (null === $id) {
            throw new NotFoundHttpException();
        }

        /** @var Customer $model */
        if (null === $model = Customer::findOne(['id' => $id])) {
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
     * @return string|\yii\web\Response
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionCreate($user = null)
    {
        $user = User::USER_GUEST !== intval($user)
            ? User::findOne(['id' => intval($user)])
            : null;
        $customer = Customer::createEmptyCustomer(null === $user ? 0 : $user->id);

        if (true === \Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
            if ($customer->load($data) && $customer->save()) {
                if (!empty($customer->getPropertyGroup())) {
                    $customer->getPropertyGroup()->appendToObjectModel($customer);
                    $data[$customer->getAbstractModel()->formName()] = isset($data['CustomerNew']) ? $data['CustomerNew'] : [];
                }
                $customer->saveModelWithProperties($data);
                $customer->refresh();
                return $this->redirect(Url::toRoute(['edit', 'id' => $customer->id]));
            }
        }

        return $this->render('create', [
            'model' => $customer,
        ]);
    }

    /**
     * @return array
     */
    public function actionAjaxUser()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'more' => false,
            'results' => []
        ];
        $search = \Yii::$app->request->get('search', []);
        if (!empty($search['term'])) {
            $query = User::find()
                ->select('id, username, first_name, email')
                ->where(['like', 'username', trim($search['term'])])
                ->orWhere(['like', 'email', trim($search['term'])])
                ->orWhere(['like', 'first_name', trim($search['term'])])
                ->asArray();

            $result['results'] = array_values($query->all());
        }

        return $result;
    }
}