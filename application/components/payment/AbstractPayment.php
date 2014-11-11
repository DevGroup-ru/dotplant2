<?php

namespace app\components\payment;

use app\models\Order;
use app\models\OrderTransaction;
use yii\base\Widget;
use yii\web\NotFoundHttpException;

abstract class AbstractPayment extends Widget
{
    /**
     * @param Order $order
     * @param OrderTransaction $transaction
     * @return string
     */
    abstract public function content($order, $transaction);

    abstract public function checkResult();

    /**
     * @param bool $success
     * @param integer|null $orderId
     */
    protected function redirect($success, $orderId = null)
    {
        $url = ['/cart/payment-' . ($success ? 'success' : 'error')];
        if (!is_null($orderId)) {
            $url['id'] = $orderId;
        }
        \Yii::$app->response->redirect($url);
    }

    /**
     * @param integer $id
     * @return OrderTransaction
     * @throws \yii\web\NotFoundHttpException
     */
    protected function loadTransaction($id)
    {
        $model = OrderTransaction::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        return $model;
    }

    public function __construct($params)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
