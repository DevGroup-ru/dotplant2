<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\backend\models\Notification;
use app\backend\models\OrderChat;
use app\components\Helper;
use app\components\SearchModel;
use app\modules\core\helpers\EventSubscribingHelper;
use app\modules\shop\events\OrderCalculateEvent;
use app\modules\shop\helpers\PriceHelper;
use app\modules\shop\models\Contragent;
use app\modules\shop\models\Customer;
use app\modules\shop\models\DeliveryInformation;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderDeliveryInformation;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\PaymentType;
use app\modules\shop\models\Product;
use app\modules\shop\models\ShippingOption;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\ShopModule;
use app\modules\user\models\User;
use app\properties\HasProperties;
use kartik\helpers\Html;
use Yii;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class BackendOrderController extends BackendController
{
    /**
     * @property ShopModule $module
     */

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionDownloadFile($key, $orderId)
    {
        $order = Order::findOne($orderId);
        if ($order === null) {
            throw new NotFoundHttpException('Order not found');
        }
        $prop = $order->getPropertyValuesByKey($key);
        if (empty($prop->values) === false) {
            $fileName = Yii::getAlias(Yii::$app->getModule('core')->visitorsFileUploadPath) . DIRECTORY_SEPARATOR . ArrayHelper::getValue($prop, 'values.0.value', '');
            if (file_exists($fileName)) {
                return Yii::$app->response->sendFile($fileName);
            }
        }
        throw new NotFoundHttpException(Yii::t('app', 'File not found'));
    }


    protected function getManagersList()
    {
        $managers = Yii::$app->cache->get('ManagersList');
        if ($managers === false) {
            $managers = User::find()
                ->join(
                    'INNER JOIN',
                    '{{%auth_assignment}}',
                    '{{%auth_assignment}}.user_id = ' . User::tableName() . '.id'
                )
                ->where(['{{%auth_assignment}}.item_name' => 'manager'])
                ->all();
            $managers = ArrayHelper::map($managers, 'id', 'username');
            Yii::$app->cache->set(
                'ManagersList',
                $managers,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(User::className())
                        ],
                    ]
                )
            );
        }
        return $managers;
    }

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

    public function beforeAction($action)
    {
        if (false === parent::beforeAction($action)) {
            return false;
        }

        EventSubscribingHelper::specialEventCallback(OrderCalculateEvent::className(),
            function (OrderCalculateEvent $event)
            {
                if (OrderCalculateEvent::AFTER_CALCULATE !== $event->state) {
                    return null;
                }

                /** @var OrderTransaction $transaction */
                $transaction = OrderTransaction::findLastByOrder(
                    $event->order,
                    null,
                    false,
                    false,
                    [OrderTransaction::TRANSACTION_START]
                );

                if (!empty($transaction)) {
                    $transaction->total_sum = $event->order->total_price;
                    $transaction->save();
                }
            }
        );

        return true;
    }


    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModelConfig = [
            'defaultOrder' => ['id' => SORT_DESC],
            'model' => Order::className(),
            'relations' => ['user' => ['username']],
            'partialMatchAttributes' => ['start_date', 'end_date', 'user_username'],
            'additionalConditions' => [],
        ];

        if (intval($this->module->showDeletedOrders) === 0) {
            $searchModelConfig['additionalConditions'] = [['is_deleted' => 0]];
        }

        /** @var SearchModel $searchModel */
        $searchModel = new SearchModel($searchModelConfig);
        if (intval($this->module->defaultOrderStageFilterBackend) > 0) {
            $searchModel->order_stage_id = intval($this->module->defaultOrderStageFilterBackend);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'managers' => $this->getManagersList(),
                'orderStages' => Helper::getModelMap(OrderStage::className(), 'id', 'name_short'),
                'paymentTypes' => Helper::getModelMap(PaymentType::className(), 'id', 'name'),
                'searchModel' => $searchModel,
                'shippingOptions' => Helper::getModelMap(ShippingOption::className(), 'id', 'name'),
            ]
        );
    }

    /**
     * Displays a single Order model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $orderIsImmutable = $model->getImmutability(\app\modules\shop\models\Order::IMMUTABLE_MANAGER);

        $transactionsDataProvider = new ArrayDataProvider([
            'allModels' => $model->transactions,
        ]);

        if (Yii::$app->request->isPost && !$orderIsImmutable) {
            $model->setScenario('backend');
            if ($model->load(Yii::$app->request->post())) {
                /** @var OrderDeliveryInformation $orderDeliveryInformation */
                $orderDeliveryInformation = $model->orderDeliveryInformation;
                if ($orderDeliveryInformation->load(Yii::$app->request->post())) {
                    $orderDeliveryInformation->saveModelWithProperties(Yii::$app->request->post());
                }

                $model->save();
            }
        }
        $lastMessages = OrderChat::find()
            ->where(['order_id' => $id])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        return $this->render(
            'view',
            [
                'lastMessages' => $lastMessages,
                'managers' => $this->getManagersList(),
                'model' => $model,
                'transactionsDataProvider' => $transactionsDataProvider,
                'orderIsImmutable' => $orderIsImmutable,
            ]
        );
    }

    /**
     * Update order status action.
     * @param integer|null $id
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateStage($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        if ($id === null) {
            if (!isset($post['editableIndex'], $post['editableKey'],
                $post['Order'][$post['editableIndex']]['order_stage_id'])) {
                throw new BadRequestHttpException;
            }
            $id = $post['editableKey'];
            $value = $post['Order'][$post['editableIndex']]['order_stage_id'];
        } else {
            if (!isset($post['Order']['order_stage_id'])) {
                throw new BadRequestHttpException;
            }
            $value = $post['Order']['order_stage_id'];
        }
        $order = $this->findModel($id);
        $order->order_stage_id = $value;
        /** @var OrderStage $orderStage */
        $orderStage = OrderStage::findOne($value);
        if ($orderStage === null || !$order->save(true, ['order_stage_id'])) {
            return [
                'message' => Yii::t('app', 'Cannot change order stage'),
            ];
        }
        return [
            'output' => Html::tag('span', $orderStage->name_short),
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdateShippingOption($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        if (!isset($post['OrderDeliveryInformation']['shipping_option_id'])) {
            throw new BadRequestHttpException;
        }
        /** @var OrderDeliveryInformation $orderDeliveryInformation */
        $orderDeliveryInformation = OrderDeliveryInformation::findOne($id);
        if (is_null($orderDeliveryInformation)) {
            throw new NotFoundHttpException;
        }
        $value = $post['OrderDeliveryInformation']['shipping_option_id'];
        $orderDeliveryInformation->shipping_option_id = $value;
        /** @var ShippingOption $shippingOption */
        $shippingOption = ShippingOption::findOne($value);
        // @todo Need to save shipping price
        if (is_null($shippingOption) || !$orderDeliveryInformation->save(true, ['shipping_option_id'])) {
            return [
                'message' => Yii::t('app', 'Cannot change shipping option'),
            ];
        }
        return [
            'output' => $shippingOption->name,
        ];
    }

    /**
     * @param integer $id
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionChangeManager($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->findModel($id);
        if (!isset($_POST['Order']['manager_id'])) {
            throw new BadRequestHttpException;
        }
        /** @var \app\modules\user\models\User|null $user */
        $user = User::findOne($_POST['Order']['manager_id']);
        if (is_null($user) || !Yii::$app->authManager->checkAccess($user->id, 'order manage')) {
            throw new BadRequestHttpException;
        }
        $order->scenario = 'changeManager';
        $oldManager = $order->manager;
        $order->load($_POST);
        if (!$order->save()) {
            return [
                'message' => Yii::t('app', 'Cannot change manager'),
            ];
        }
        if (is_null($oldManager) || $oldManager->id != $order->manager_id) {
            // send message
            try {
                $to = [$user->email];
                if (!is_null($oldManager)) {
                    $to[] = $oldManager->email;
                    $subject =  Yii::t(
                        'app',
                        'Manager has been changed from {oldManagerName} to {newManagerName}. Order #{orderId}',
                        [
                            'oldManagerName' => $oldManager->getDisplayName(),
                            'newManagerName' => $user->getDisplayName(),
                            'orderId' => $order->id,
                        ]
                    );
                    Notification::addNotification(
                        $oldManager->id,
                        Yii::t(
                            'app',
                            'You are not a manager of <a href="{orderUrl}" target="_blank">order #{orderId}</a> already',
                            [
                                'orderId' =>$order->id,
                                'orderUrl' => Url::toRoute(['/backend/order/view', 'id' => $order->id]),
                            ]
                        ),
                        'Order',
                        'info'
                    );
                } else {
                    $subject =  Yii::t(
                        'app',
                        'Manager has been changed to {newManagerName}. Order #{orderId}',
                        [
                            'newManagerName' => $user->getDisplayName(),
                            'orderId' => $order->id,
                        ]
                    );
                }
                Notification::addNotification(
                    $user->id,
                    Yii::t(
                        'app',
                        'You are a new manager of <a href="{orderUrl}" target="_blank">order #{orderId}</a>',
                        [
                            'orderId' =>$order->id,
                            'orderUrl' => Url::toRoute(['/backend/order/view', 'id' => $order->id]),
                        ]
                    ),
                    'Order',
                    'info'
                );
                Yii::$app->mail
                    ->compose(
                        '@app/backend/views/order/change-manager-email-template',
                        [
                            'manager' => $user,
                            'oldManager' => $oldManager,
                            'order' => $order,
                            'user' => Yii::$app->user->getIdentity(),
                        ]
                    )
                    ->setTo($to)
                    ->setFrom(Yii::$app->mail->transport->getUsername())
                    ->setSubject($subject)
                    ->send();
            } catch (\Exception $e) {
                // do nothing
            }
        }
        return [
            'output' => Html::encode($user->username),
        ];
    }

    public function actionDelete($id = null)
    {
        /** @var Order $model */
        if ((null === $id) || (null === $model = Order::findOne($id))) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                Url::toRoute(['index'])
            )
        );
    }

    public function actionDeleteOrderItem($id)
    {
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::findOne($id);
        if (is_null($orderItem)) {
            throw new NotFoundHttpException();
        }
        $orderItem->delete();
        $orderItem->order->calculate(true);
        return $this->redirect(['view', 'id' => $orderItem->order->id]);
    }

    public function actionChangeOrderItemQuantity($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::findOne($id);
        $orderItem->load(Yii::$app->request->post());
        $orderItem->quantity = $orderItem->product->measure->ceilQuantity($orderItem->quantity);
        $orderItem->price_per_pcs = PriceHelper::getProductPrice(
            $orderItem->product,
            $orderItem->order,
            1,
            SpecialPriceList::TYPE_CORE
        );
        if (!$orderItem->save(true, ['quantity', 'total_price', 'discount_amount', 'total_price_without_discount'])
            || !$orderItem->order->calculate(true)
        ) {
            return [
                'message' => Yii::t('app', 'Cannot change quantity'),
                'error' => $orderItem->errors,
            ];
        }
        return [
            'output' => $orderItem->quantity,
        ];
    }

    public function actionAutoCompleteSearch($orderId, $term, $parentId = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Product::find()->orderBy('sort_order');
        foreach (['name', 'content'] as $attribute) {
            $query->orWhere(['like', $attribute, $term]);
        }
        $products = $query->limit(20)->all();
        $result = [];
        /** @var Product $product */
        foreach ($products as $product) {
            $result[] = [
                'name' => $product->name,
                'url' => Url::toRoute([
                    'add-product',
                    'orderId' => $orderId,
                    'productId' => $product->id,
                    'parentId' => $parentId,
                ]),
            ];
        }
        return $result;
    }

    public function actionAddProduct($orderId, $productId, $parentId = 0)
    {
        $order = $this->findModel($orderId);
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::findOne(['product_id' => $productId, 'order_id' => $orderId]);
        /** @var Product $product */
        $product = Product::findById($productId);
        if (is_null($orderItem)) {
            $orderItem = new OrderItem;
            $orderItem->attributes = [
                'parent_id' => $parentId,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $product->measure->nominal,
                'price_per_pcs' =>  PriceHelper::getProductPrice(
                    $product,
                    $order,
                    1,
                    SpecialPriceList::TYPE_CORE
                ),
            ];
        } else {
            $orderItem->quantity++;
        }
        $orderItem->save();
        $order->calculate(true);
        return $this->redirect(['view', 'id' => $orderId]);
    }

    public function actionUpdateOrderProperties($id)
    {
        /** @var Order|HasProperties $model */
        $model = $this->findModel($id);
        $model->abstractModel->setAttributesValues(Yii::$app->request->post());
        if ($model->abstractModel->validate()) {
            $model->getPropertyGroups(true);
            $model->saveProperties(Yii::$app->request->post());
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @return string|Response
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionCreate()
    {
        $model = Order::create(false, false, true);
        $model->setScenario('backend');

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($model->load($data) && $model->validate()) {
                if (User::USER_GUEST === intval($model->user_id)) {
                    $model->customer_id = 0;
                } elseif (null === Customer::findOne(['user_id' => intval($model->user_id), 'id' => intval($model->customer_id)])) {
                    $model->customer_id = 0;
                }

                if (0 === intval($model->customer_id)) {
                    $customer = Customer::createEmptyCustomer(intval($model->user_id));
                    if ($customer->load($data) && $customer->save()) {
                        if (!empty($customer->getPropertyGroup())) {
                            $customer->getPropertyGroup()->appendToObjectModel($customer);
                            $data[$customer->getAbstractModel()->formName()] = isset($data['CustomerNew']) ? $data['CustomerNew'] : [];
                        }
                        $customer->saveModelWithProperties($data);
                        $customer->refresh();
                        $model->customer_id = $customer->id;
                    }
                } else {
                    $customer = Customer::findOne(['id' => $model->customer_id]);
                }

                if (0 === $model->contragent_id || null === Contragent::findOne(['id' => $model->contragent_id, 'customer_id' => $model->customer_id])) {
                    $contragent = Contragent::createEmptyContragent($customer);
                    if ($contragent->load($data) && $contragent->save()) {
                        if (!empty($contragent->getPropertyGroup())) {
                            $contragent->getPropertyGroup()->appendToObjectModel($contragent);
                            $data[$contragent->getAbstractModel()->formName()] = isset($data['ContragentNew']) ? $data['ContragentNew'] : [];
                        }
                        $contragent->saveModelWithProperties($data);
                        $contragent->refresh();
                        $model->contragent_id = $contragent->id;
                    }
                } else {
                    $contragent = Contragent::findOne(['id' => $model->contragent_id]);
                }

                if ($model->save()) {
                    OrderDeliveryInformation::createNewOrderDeliveryInformation($model, false);
                    DeliveryInformation::createNewDeliveryInformation($contragent, false);
                    return $this->redirect(Url::toRoute([
                        'view', 'id' => $model->id
                    ]));
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdatePaymentType($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        if (!isset($post['Order']['payment_type_id'])) {
            throw new BadRequestHttpException;
        }
        $value = $post['Order']['payment_type_id'];
        $order = $this->findModel($id);
        $order->payment_type_id = $value;
        /** @var PaymentType $paymentType */
        $paymentType = PaymentType::findOne($value);
        if ($paymentType === null || !$order->save(true, ['payment_type_id'])) {
            return [
                'message' => Yii::t('app', 'Cannot change a payment type'),
            ];
        }
        return [
            'output' => Html::tag('span', $paymentType->name),
        ];
    }

    /**
     * Add new message to OrderChat
     * @param $orderId
     * @return int[]
     * @throws BadRequestHttpException
     */
    public function actionSendToOrderChat($orderId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Order $order */
        $order = Order::findOne($orderId);
        if (null === $order) {
            throw new BadRequestHttpException();
        }

        $message = new OrderChat();
        $message->loadDefaultValues();
        $message->message = Yii::$app->request->post('message');
        $message->order_id = $order->id;
        $message->user_id = Yii::$app->user->id;
        if ($message->save()) {
            if ($order->manager_id != Yii::$app->user->id) {
                Notification::addNotification(
                    $order->manager_id,
                    Yii::t(
                        'app',
                        'Added a new comment to <a href="{orderUrl}" target="_blank">order #{orderId}</a>',
                        [
                            'orderUrl' => Url::toRoute(['/backend/order/view', 'id' => $order->id]),
                            'orderId' => $order->id,
                        ]
                    ),
                    'Order',
                    'info'
                );
            }
            $message->refresh();
            $user = $message->user;
            return [
                'status' => 1,
                'message' => $message->message,
                'user' => null !== $user ? $user->username : Yii::t('app', 'Unknown'),
                'gravatar' => null !== $user ? $user->gravatar() : null,
                'date' => $message->date,
            ];
        }

        return ['status' => 0];
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
                ->select('id, username, first_name, last_name, email')
                ->where(['like', 'username', trim($search['term'])])
                ->orWhere(['like', 'email', trim($search['term'])])
                ->orWhere(['like', 'first_name', trim($search['term'])])
                ->orWhere(['like', 'last_name', trim($search['term'])])
                ->asArray();

            $result['results'] = array_values($query->all());
        }

        return $result;
    }

    /**
     * @return array
     */
    public function actionAjaxCustomer($template = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'more' => false,
            'results' => []
        ];
        $search = \Yii::$app->request->get('search', []);
        $user_id = isset($search['user']) && User::USER_GUEST !== intval($search['user']) ? intval($search['user']) : User::USER_GUEST;
        if (!empty($search['term'])) {
            $query = Customer::find()
                ->select('id, first_name, middle_name, last_name, email, phone')
                ->where(['user_id' => $user_id])
                ->andWhere('first_name LIKE :term1 OR middle_name LIKE :term2 OR last_name LIKE :term3 OR email LIKE :term4 OR phone LIKE :term5', [
                    ':term1' => '%'.trim($search['term']).'%',
                    ':term2' => '%'.trim($search['term']).'%',
                    ':term3' => '%'.trim($search['term']).'%',
                    ':term4' => '%'.trim($search['term']).'%',
                    ':term5' => '%'.trim($search['term']).'%',
                ])
                ->asArray();

            $result['results'] = array_values($query->all());
        }

        if (!empty($result['results']) && 'simple' === $template) {
            $result['cards'] = array_reduce($result['results'],
                function ($result, $item)
                {
                    /** @var array $item */
                    $result[$item['id']] = \app\modules\shop\widgets\Customer::widget([
                        'viewFile' => 'customer/backend_list',
                        'model' => Customer::findOne(['id' => $item['id']]),
                    ]);
                    return $result;
                }, []);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function actionAjaxContragent($template = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = [
            'more' => false,
            'results' => [[0 => Yii::t('app', 'New contragent')]]
        ];
        $search = \Yii::$app->request->get('search', []);
        $customer_id = isset($search['customer']) ? intval($search['customer']) : 0;
        $query = Contragent::find()
            ->select('id, type')
            ->where(['customer_id' => $customer_id])
            ->asArray();
        $result['results'] = array_merge(array_values($query->all()), $result['results']);

        if (!empty($result['results']) && 'simple' === $template) {
            $result['cards'] = array_reduce($result['results'],
                function ($result, $item)
                {
                    /** @var array $item */
                    if (!empty($item['id'])) {
                        $result[$item['id']] = \app\modules\shop\widgets\Contragent::widget([
                            'viewFile' => 'contragent/backend_list',
                            'model' => Contragent::findOne(['id' => $item['id']]),
                        ]);
                    }
                    return $result;
                }, []);
        }

        return $result;
    }
}
