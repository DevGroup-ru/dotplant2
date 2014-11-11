<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class FutubankPayment extends AbstractPayment
{
    protected $currency;
    protected $merchant;
    protected $secretKey;
    protected $testing;

    private function getSignature($data)
    {
        ksort($data);
        $chunks = array();
        foreach ($data as $key => $value) {
            if ($value && ($key != 'signature')) {
                $chunks[] = $key . '=' . base64_encode($value);
            }
        }
        return sha1($this->secretKey . sha1($this->secretKey . implode('&', $chunks)));
    }

    private function generateSalt($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $result;
    }

    public function content($order, $transaction)
    {
        $formData = [
            'client_email' => '',
            'client_name' => '',
            'client_phone' => '',
            'merchant' => $this->merchant,
            'unix_timestamp' => time(),
            'salt' => $this->generateSalt(32),
            'amount' => $transaction->total_sum,
            'currency' => $this->currency,
            'description' => 'Order #' . $order->id,
            'order_id' => $transaction->id,
            'success_url' => Url::toRoute(['/cart/payment-success', 'id' => $order->id], true),
            'fail_url' => Url::toRoute(['/cart/payment-fail', 'id' => $order->id], true),
            'cancel_url' => Url::toRoute(['/cart/payment-type', 'id' => $order->id], true),
            'meta' => Json::encode(['transactionId' => $transaction->id]),
        ];
        if ($this->testing == 1) {
            $formData['testing'] = 1;
        }
        $formData['signature'] = $this->getSignature($formData);
        return $this->render(
            'futubank',
            [
                'formData' => $formData,
                'order' => $order,
                'transaction' => $transaction,
            ]
        );
    }

    public function checkResult()
    {
        if (isset($_POST['order_id'], $_POST['state'], $_POST['transaction_id'], $_POST['signature'])) {
            $transaction = $this->loadTransaction($_POST['order_id']);
            $transaction->result_data = Json::encode($_POST);
            if ($this->getSignature($_POST) == $_POST['signature']) {
                $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
                if ($transaction->save(true, ['status', 'result_data'])) {
                    echo 'OK ' . $transaction->id;
                } else {
                    throw new HttpException(500);
                }
            } else {
                $transaction->status = OrderTransaction::TRANSACTION_ERROR;
                throw new BadRequestHttpException;
            }
        } else {
            throw new BadRequestHttpException;
        }
    }
}
