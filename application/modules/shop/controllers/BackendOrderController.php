<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\backend\models\Notification;
use app\backend\models\OrderChat;
use app\components\Helper;
use app\components\SearchModel;
use app\models\Config;
use app\modules\shop\helpers\PriceHelper;
use app\modules\shop\models\Contragent;
use app\modules\shop\models\Customer;
use app\modules\shop\models\DeliveryInformation;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderDeliveryInformation;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\PaymentType;
use app\modules\shop\models\Product;
use app\modules\shop\models\ShippingOption;
use app\modules\shop\models\SpecialPriceList;
use app\modules\user\models\User;
use app\properties\HasProperties;
use kartik\helpers\Html;
use Yii;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
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
        ];
        if (Config::getValue('shop.showDeletedOrders', 0) === 0) {
            $searchModelConfig['additionalConditions'] = [['is_deleted' => 0]];
        }
        $searchModel = new SearchModel($searchModelConfig);
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

        $transactionsDataProvider = new ArrayDataProvider([
            'allModels' => $model->transactions,
        ]);

        $message = new OrderChat();
        if (Yii::$app->request->isPost) {
            $model->setScenario('backend');
            if ($model->load(Yii::$app->request->post())) {
                $customer = $model->customer;
                if ($customer->load(Yii::$app->request->post()) && $customer->save()) {
                    $customer->saveProperties(Yii::$app->request->post());
                    $model->customer_id = $customer->id;
                }

                $contragent = !empty($model->contragent) ? $model->contragent : Contragent::createEmptyContragent($customer, false);
                if ($contragent->load(Yii::$app->request->post()) && $contragent->validate()) {
                    $_isNewRecord = $contragent->isNewRecord;
                    $_data = Yii::$app->request->post();
                    if ($contragent->save()) {
                        if ($_isNewRecord && !empty($contragent->getPropertyGroup())) {
                            $contragent->getPropertyGroup()->appendToObjectModel($contragent);
                            $_data[$contragent->getAbstractModel()->formName()] = isset($_data['ContragentNew']) ? $_data['ContragentNew'] : [];
                        }
                        $contragent->saveProperties($_data);
                        $contragent->refresh();
                        $model->contragent_id = $contragent->id;
                    }
                }

                $deliveryInformation = !empty($contragent->deliveryInformation) ? $contragent->deliveryInformation : DeliveryInformation::createNewDeliveryInformation($contragent, false);
                if ($deliveryInformation->load(Yii::$app->request->post())) {
                    $deliveryInformation->save();
                }

                $orderDeliveryInformation = $model->orderDeliveryInformation;
                if ($orderDeliveryInformation->load(Yii::$app->request->post()) && $orderDeliveryInformation->save()) {
                    $orderDeliveryInformation->saveProperties(Yii::$app->request->post());
                }

                $model->save();
            }

            $message->load(Yii::$app->request->post());
            $message->order_id = $id;
            $message->user_id = Yii::$app->user->id;
            if ($message->save()) {
                if ($model->manager_id != Yii::$app->user->id) {
                    Notification::addNotification(
                        $model->manager_id,
                        Yii::t(
                            'app',
                            'Added a new comment to <a href="{orderUrl}" target="_blank">order #{orderId}</a>',
                            [
                                'orderUrl' => Url::toRoute(['/backend/order/view', 'id' => $model->id]),
                                'orderId' => $model->id,
                            ]
                        ),
                        'Order',
                        'info'
                    );
                }
            }

            $this->refresh();
        }
        $lastMessages = OrderChat::find()
            ->where(['order_id' => $id])
            ->orderBy('`id` DESC')
            ->all();
        return $this->render(
            'view',
            [
                'lastMessages' => $lastMessages,
                'managers' => $this->getManagersList(),
                'message' => $message,
                'model' => $model,
                'transactionsDataProvider' => $transactionsDataProvider,
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
        if (!$orderItem->load(Yii::$app->request->post()) || !$orderItem->save(true, ['quantity'])
            || !$orderItem->order->calculate(true)
        ) {
            return [
                'message' => Yii::t('app', 'Cannot change quantity'),
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
        foreach ($products as $product) {
            $result[] = [
                'template' => $this->renderPartial(
                    'auto-complete-item-template',
                    [
                        'orderId' => $orderId,
                        'product' => $product,
                        'parentId' => $parentId,
                    ]
                ),
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
            $totalPriceWithoutDiscount = PriceHelper::getProductPrice(
                $product,
                $order,
                $product->measure->nominal,
                SpecialPriceList::TYPE_CORE
            );
            $totalPrice = PriceHelper::getProductPrice($product, $order, $product->measure->nominal);
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
                'total_price_without_discount' => $totalPriceWithoutDiscount,
                'total_price' =>  $totalPrice,
                'discount_amount' => $totalPriceWithoutDiscount - $totalPrice
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
        $model->abstractModel->setAttrubutesValues(Yii::$app->request->post());
        if ($model->abstractModel->validate()) {
            $model->getPropertyGroups(true);
            $model->saveProperties(Yii::$app->request->post());
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionCreate($returnUrl = ['index'])
    {
        $model = Order::create(false, false);
        if (!is_null($model)) {
            $customer = !empty(Customer::getCustomerByUserId($model->user_id)) ? Customer::getCustomerByUserId($model->user_id) : Customer::createEmptyCustomer($model->user_id, false);
            $model->customer_id = $customer->id;
            OrderDeliveryInformation::createNewOrderDeliveryInformation($model, false);
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            Yii::$app->session->addFlash('error', Yii::t('app', 'Cannot create a new order.'));
            return $this->redirect($returnUrl);
        }
    }

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
}
