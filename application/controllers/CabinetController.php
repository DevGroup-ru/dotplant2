<?php

namespace app\controllers;

use Yii;
use app\models\Order;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\User;
use yii\base\Security;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CabinetController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['order'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionOrder($id)
    {
        $order = Order::findOne(['hash' => $id]);
        if (is_null($order)) {
            throw new NotFoundHttpException;
        }
        return $this->render('order', ['order' => $order]);
    }

    public function actionOrders()
    {
        $query = Order::find();
        $needToSearch = true;
        if (!Yii::$app->user->isGuest) {
            $query->AndWhere(['user_id' => Yii::$app->user->id]);
        } else {
            if (Yii::$app->session->has('orders')) {
                $query->andWhere(['id', 'in', Yii::$app->session->get('orders')]);
            } else {
                $needToSearch = false;
            }
        }
        if ($needToSearch) {
            $orders = $query->all();
        } else {
            $orders = [];
        }
        $canceledOrders = [];
        $currentOrders = [];
        $doneOrders = [];
        foreach ($orders as $order) {
            switch ($order->order_status_id) {
                case 6:
                    $doneOrders[] = $order;
                    break;
                case 7:
                    $canceledOrders[] = $order;
                    break;
                default:
                    $currentOrders[] = $order;
            }
        }
        unset($orders);
        return $this->render(
            'orders',
            [
                'canceledOrders' => $canceledOrders,
                'currentOrders' => $currentOrders,
                'doneOrders' => $doneOrders,
            ]
        );
    }

    public function actionProfile()
    {
        $model = User::findOne(Yii::$app->user->id);
        $model->scenario = 'updateProfile';
        $model->abstractModel->setAttrubutesValues(Yii::$app->request->post());
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->abstractModel->validate()) {
            if ($model->save()) {
                $model->getPropertyGroups(true);
                $model->saveProperties(Yii::$app->request->post());
                Yii::$app->session->setFlash('success', Yii::t('app', 'Your profile has been updated'));
                $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Internal error'));
            }
        }
        $propertyGroups = PropertyGroup::getForModel($model->getObject()->id, $model->id);
        $properties = [];
        foreach ($propertyGroups as $propertyGroup) {
            $properties[$propertyGroup->id] = [
                'group' => $propertyGroup,
                'properties' => Property::getForGroupId($propertyGroup->id),
            ];
        }
        unset($propertyGroups);
        return $this->render(
            'profile',
            [
                'model' => $model,
                'propertyGroups' => $properties,
                'services' => ArrayHelper::map($model->services, 'id', 'service_type'),
            ]
        );
    }

    public function actionChangePassword()
    {
        $model = User::findOne(Yii::$app->user->id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->scenario = 'changePassword';
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $formIsValid = $model->validate();
            $passwordIsValid = $model->validatePassword($model->password);
            if (!$passwordIsValid) {
                $model->addError('password', Yii::t('app', 'Wrong password'));
            }
            if ($formIsValid && $passwordIsValid) {
                $security = new Security;
                $model->password_hash = $security->generatePasswordHash($model->newPassword);
                if ($model->save(true, ['password_hash'])) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Password has been changed'));
                    $this->refresh();
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Internal error'));
                }
            }
        }
        return $this->render('change-password', ['model' => $model]);
    }
}
