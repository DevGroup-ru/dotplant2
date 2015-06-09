<?php

namespace app\modules\shop\controllers;

use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\shop\models\Contragent;
use app\modules\shop\models\Customer;
use app\modules\shop\models\Order;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

class CabinetController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'update-order'],
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
            [
                'class' => DisableRobotIndexBehavior::className(),
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string
     */
    public function actionProfile()
    {
        Url::remember('', '__shopCabinetUpdateReturnUrl');
        return $this->render('profile');
    }

    /**
     * @param string $hash
     * @return \yii\web\Response
     */
    public function actionUpdateOrder($hash = '')
    {
        $userId = \Yii::$app->user->isGuest ? 0 : \Yii::$app->user->id;
        /** @var Order $order */
        if (null === $order = Order::findOne(['user_id' => $userId, 'hash' => $hash])) {
            return $this->redirect(Url::previous('__shopCabinetUpdateGuestReturnUrl'));
        }

        $this->updateCustomer($order->customer);
        $this->updateContragent($order->contragent);

        return $this->redirect(Url::previous('__shopCabinetUpdateGuestReturnUrl'));
    }

    public function actionUpdate()
    {
        return $this->redirect(Url::previous('__shopCabinetUpdateReturnUrl'));
    }

    private function updateCustomer($customer)
    {
        if ($customer === null) {
            return;
        }
        if ($customer->load(\Yii::$app->request->post())) {
            $customer->saveModelWithProperties(\Yii::$app->request->post());
        }
    }

    private function updateContragent($contragent)
    {
        if ($contragent === null) {
            return;
        }
        if ($contragent->load(\Yii::$app->request->post())) {
            $contragent->saveModelWithProperties(\Yii::$app->request->post());
        }
    }
}
?>