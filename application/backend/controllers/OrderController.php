<?php

namespace app\backend\controllers;

use app\backend\models\Notification;
use app\backend\models\OrderChat;
use app\components\Helper;
use app\components\SearchModel;
use app\models\Order;
use app\models\OrderItem;
use app\models\OrderStatus;
use app\models\OrderTransaction;
use app\models\PaymentType;
use app\models\Product;
use app\models\ShippingOption;
use app\modules\user\models\User;
use kartik\helpers\Html;
use Yii;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
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
        $searchModel = new SearchModel(
            [
                'defaultOrder' => ['id' => SORT_DESC],
                'model' => Order::className(),
                'relations' => ['user' => ['username']],
                'partialMatchAttributes' => ['start_date', 'end_date', 'user_username'],
            ]
        );
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'managers' => $this->getManagersList(),
                'orderStatuses' => Helper::getModelMap(OrderStatus::className(), 'id', 'short_title'),
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
                $this->refresh();
            }
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
    public function actionUpdateStatus($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        if ($id === null) {
            if (!isset($post['editableIndex'], $post['editableKey'],
                $post['Order'][$post['editableIndex']]['order_status_id'])) {
                throw new BadRequestHttpException;
            }
            $id = $post['editableKey'];
            $value = $post['Order'][$post['editableIndex']]['order_status_id'];
        } else {
            if (!isset($post['Order']['order_status_id'])) {
                throw new BadRequestHttpException;
            }
            $value = $post['Order']['order_status_id'];
        }
        $order = $this->findModel($id);
        $order->order_status_id = $value;
        if (in_array($value, [6, 7])) {
            $order->end_date = new Expression('NOW()');
        }
        $orderStatus = OrderStatus::findOne($value);
        if ($orderStatus === null || !$order->save(true, ['order_status_id', 'end_date'])) {
            return [
                'message' => Yii::t('app', 'Cannot change order status'),
            ];
        }
        return [
            'output' => Html::tag('span', $orderStatus->short_title, ['class' => $orderStatus->label]),
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
        if (!isset($post['Order']['shipping_option_id'])) {
            throw new BadRequestHttpException;
        }
        $value = $post['Order']['shipping_option_id'];
        $order = $this->findModel($id);
        $order->shipping_option_id = $value;
        $shippingOption = ShippingOption::findOne($value);
        if (is_null($shippingOption) || !$order->save(true, ['shipping_option_id'])) {
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

    /*
     *
     */
    public function actionDelete($id = null)
    {
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

    public function actionRestore($id = null)
    {
        if (null === $id) {
            new NotFoundHttpException();
        }

        if (null === $model = Order::findOne(['id' => $id])) {
            new NotFoundHttpException();
        }

        $model->restoreFromTrash();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Object successfully restored'));

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                Url::toRoute(['view', 'id' => $id])
            )
        );
    }

    public function actionDeleteOrderItem($id)
    {
        $orderItem = OrderItem::findOne($id);
        if (is_null($orderItem)) {
            throw new NotFoundHttpException();
        }
        $orderItem->delete();
        $orderItem->order->reCalc();
        return $this->redirect(['view', 'id' => $orderItem->order->id]);
    }

    public function actionChangeOrderItemQuantity($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderItem = OrderItem::findOne($id);
        if (!$orderItem->load(Yii::$app->request->post()) || !$orderItem->save(true, ['quantity'])
            || !$orderItem->order->reCalc()
        ) {
            return [
                'message' => Yii::t('app', 'Cannot change quantity'),
            ];
        }
        return [
            'output' => $orderItem->quantity,
        ];
    }

    public function actionAutoCompleteSearch($orderId, $term)
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
                    ]
                ),
            ];
        }
        return $result;
    }

    public function actionAddProduct($orderId, $productId)
    {
        $order = $this->findModel($orderId);
        $orderItem = OrderItem::findOne(['product_id' => $productId, 'order_id' => $orderId]);
        if (is_null($orderItem)) {
            $orderItem = new OrderItem;
            $orderItem->attributes = [
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => 1,
            ];
        } else {
            $orderItem->quantity++;
        }
        $orderItem->save();
        $order->reCalc();
        return $this->redirect(['view', 'id' => $orderId]);
    }

    public function actionUpdateOrderProperties($id)
    {
        $model = $this->findModel($id);
        $model->abstractModel->setAttrubutesValues(Yii::$app->request->post());
        if ($model->abstractModel->validate()) {
            $model->getPropertyGroups(true);
            $model->saveProperties(Yii::$app->request->post());
        }
        return $this->redirect(['view', 'id' => $id]);
    }

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
}
