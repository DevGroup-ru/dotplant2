<?php

namespace app\backend\controllers;

use Yii;
use app\backend\models\Notification;
use app\backend\models\OrderChat;
use app\components\Helper;
use app\components\SearchModel;
use app\models\Order;
use app\models\OrderStatus;
use app\models\PaymentType;
use app\models\ShippingOption;
use app\models\User;
use kartik\helpers\Html;
use yii\caching\TagDependency;
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
                            \app\behaviors\TagDependency::getCommonTag(User::className())
                        ],
                    ]
                )
            );
        }
        return $managers;
    }

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
                            'shop',
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
                'message' => Yii::t('shop', 'Cannot change order status'),
            ];
        }
        return [
            'output' => Html::tag('span', $orderStatus->short_title, ['class' => $orderStatus->label]),
        ];
    }

    /**
     * Update order internal comment action.
     * @param integer $id
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateInternalComment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->findModel($id);
        if (!isset($_POST['Order']['internal_comment'])) {
            throw new BadRequestHttpException;
        }
        $order->internal_comment = $_POST['Order']['internal_comment'];
        if (!$order->save(true, ['internal_comment'])) {
            return [
                'message' => Yii::t('shop', 'Cannot change internal comment'),
            ];
        }
        return [
            'output' => Html::encode($order->internal_comment),
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
        $user = User::findOne($_POST['Order']['manager_id']);
        if (is_null($user) || !Yii::$app->authManager->checkAccess($user->id, 'order manage')) {
            throw new BadRequestHttpException;
        }
        $order->scenario = 'changeManager';
        $oldManager = $order->manager;
        $order->load($_POST);
        if (!$order->save()) {
            return [
                'message' => Yii::t('shop', 'Cannot change manager'),
            ];
        }
        if (is_null($oldManager) || $oldManager->id != $order->manager_id) {
            // send message
            try {
                $to = [$user->email];
                if (!is_null($oldManager)) {
                    $to[] = $oldManager->email;
                    $subject =  Yii::t(
                        'shop',
                        'Manager has been changed from {oldManagerName} to {newManagerName}. Order #{orderId}',
                        [
                            'oldManagerName' => $oldManager->getAwesomeUsername(),
                            'newManagerName' => $user->getAwesomeUsername(),
                            'orderId' => $order->id,
                        ]
                    );
                    Notification::addNotification(
                        $oldManager->id,
                        Yii::t(
                            'shop',
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
                        'shop',
                        'Manager has been changed to {newManagerName}. Order #{orderId}',
                        [
                            'newManagerName' => $user->getAwesomeUsername(),
                            'orderId' => $order->id,
                        ]
                    );
                }
                Notification::addNotification(
                    $user->id,
                    Yii::t(
                        'shop',
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
