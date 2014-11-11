<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class LiqPayPayment extends AbstractPayment
{
    protected $currency;
    protected $language;
    protected $privateKey;
    protected $publicKey;
    protected $testMode;

    protected function getSignature($data)
    {
        if ($data['currency'] == 'RUR') {
            $data['currency'] = 'RUB';
        }
        foreach (['order_id', 'type', 'result_url', 'server_url'] as $name) {
            if (!isset($data[$name])) {
                $data[$name] = '';
            }
        }
        $signature = base64_encode(
            sha1(
                $this->privateKey . $data['amount'] . $data['currency'] . $this->publicKey . $data['order_id']
                . $data['type'] . $data['description'] . $data['result_url'] . $data['server_url'],
                1
            )
        );
        return $signature;
    }

    public function content($order, $transaction)
    {
        $formData = [
            "public_key" => $this->publicKey,
            "amount" => $transaction->total_sum,
            "currency" => $this->currency,
            "description" => "Order #" . $order->id,
            "order_id" => $transaction->id,
            "result_url" => Url::toRoute(['/cart/payment-success', 'id' => $order->id], true),
            "server_url" => Url::toRoute(['/cart/payment-result', 'id' => $transaction->payment_type_id], true),
            "type" => "buy",
            "language" => $this->language,
            "sandbox" => $this->testMode,
        ];
        $formData['signature'] = $this->getSignature($formData);
        return $this->render(
            'liqpay',
            [
                'formData' => $formData,
                'order' => $order,
                'transaction' => $transaction,
            ]
        );
    }

    public function checkResult()
    {
        if (!isset($_POST['public_key'], $_POST['amount'], $_POST['currency'], $_POST['description'],
            $_POST['order_id'], $_POST['signature'] )
        ) {
            throw new BadRequestHttpException;
        }
        $transaction = $this->loadTransaction($_POST['order_id']);
        $transaction->result_data = Json::encode($_POST);
        foreach (['status', 'transaction_id', 'sender_phone'] as $name) {
            if (!isset($_POST[$name])) {
                $_POST[$name] = '';
            }
        }
        $signature = base64_encode(
            sha1(
                $this->privateKey . $_POST['amount'] . $_POST['currency']
                . $_POST['public_key'] . $_POST['order_id'] . $_POST['type'] . $_POST['description']
                . $_POST['status'] . $_POST['transaction_id'] . $_POST['sender_phone'],
                1
            )
        );
        if ($_POST['signature'] != $signature) {
            $transaction->status = OrderTransaction::TRANSACTION_ERROR;
            if ($transaction->save(true, ['status', 'result_data'])) {
                throw new HttpException(500);
            }
            throw new BadRequestHttpException;
        }
        if ($_POST['status'] == 'success') {
            $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
            if ($transaction->save(true, ['status', 'result_data'])) {
                throw new HttpException(500);
            }
            $this->redirect(true, $transaction->order_id);
        } else {
            $transaction->status = OrderTransaction::TRANSACTION_ERROR;
            if ($transaction->save(true, ['status', 'result_data'])) {
                throw new HttpException(500);
            }
            // @todo wat error
            echo 'Error';
        }
    }
}
