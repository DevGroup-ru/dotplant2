<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Url;

class CashPayment extends AbstractPayment
{
    public function content($order, $transaction)
    {
        $url = Url::toRoute(
            [
                '/cart/payment-result',
                'id' => $order->payment_type_id,
                'orderId' => $order->id,
                'transactionId' => $transaction->id,
            ]
        );
        return $this->render(
            'cash',
            [
                'order' => $order,
                'transaction' => $transaction,
                'url' => $url,
            ]
        );
    }

    public function checkResult()
    {
        if (!isset($_GET['id'], $_GET['orderId'], $_GET['transactionId'])) {
            $this->redirect(false);
        }
        $transaction = $this->loadTransaction($_GET['transactionId']);
        if (is_null($transaction->paymentType) || $transaction->paymentType->class != $this->className()) {
            $this->redirect(false, $_GET['orderId']);
        }
        $this->redirect($transaction->updateStatus(OrderTransaction::TRANSACTION_SUCCESS), $_GET['orderId']);
    }
}
