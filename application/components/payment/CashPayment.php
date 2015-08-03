<?php

namespace app\components\payment;

use Yii;
use app\modules\shop\models\OrderTransaction;

class CashPayment extends AbstractPayment
{
    public function content()
    {
        $resultUrl = $this->createResultUrl([
            'id' => $this->order->payment_type_id,
            'transactionId' => $this->transaction->id,
        ]);

//        return $this->render(
//            'cash',
//            [
//                'transaction' => $this->transaction,
//                'url' => $resultUrl,
//            ]
//        );
        $response = Yii::$app->response->redirect($resultUrl);
        Yii::$app->end(0, $response);
    }

    public function checkResult($hash = '')
    {
        $transactionId = Yii::$app->request->get('transactionId');

        /** @var OrderTransaction $transaction */
        if (null === $transaction = $this->loadTransaction($transactionId)) {
            return $this->redirect($this->createErrorUrl(['id' => $transactionId]));
        }
        if (empty($transaction->paymentType) || $transaction->paymentType->class !== $this->className()) {
            return $this->redirect($this->createErrorUrl(['id' => $transactionId]));
        }
        if (!$transaction->checkHash($hash)) {
            return $this->redirect($this->createErrorUrl(['id' => $transactionId]));
        }

        return $transaction->updateStatus(OrderTransaction::TRANSACTION_SUCCESS)
            ? $this->redirect($this->createSuccessUrl(['id' => $transactionId]))
            : $this->redirect($this->createErrorUrl(['id' => $transactionId]));
    }
}