<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class LiqPayPayment extends AbstractPayment
{
    protected $supportedCurrencies = ['EUR','UAH','USD','RUB','RUR'];
    protected $currency;
    protected $language;
    protected $privateKey;
    protected $publicKey;
    protected $testMode;

    protected function getParams($params)
    {
        $params['public_key'] = $this->publicKey;
        foreach (['version', 'amount', 'currency', 'description'] as $paramName) {
            if (!isset($params[$paramName])) {
                throw new \InvalidArgumentException($paramName . ' is null');
            }
        }
        if (!in_array($params['currency'], $this->supportedCurrencies)) {
            throw new \InvalidArgumentException('currency is not supported');
        }
        if ($params['currency'] == 'RUR') {
            $params['currency'] = 'RUB';
        }
        return $params;
    }

    protected function getSignature($str)
    {
        return base64_encode(sha1($this->privateKey . $str . $this->privateKey, 1));
    }

    public function content($order, $transaction)
    {
        $params = [
            'version' => 3,
            'amount' => $transaction->total_sum,
            'currency' => $this->currency,
            'description' => "Order #" . $order->id,
            'order_id' => $transaction->id,
            'result_url' => Url::toRoute(['/cart/payment-success', 'id' => $order->id], true),
            'server_url' => Url::toRoute(['/cart/payment-result', 'id' => $transaction->payment_type_id], true),
            'type' => 'buy',
            'language' => $this->language,
            'sandbox' => $this->testMode,
        ];
        $data = base64_encode(Json::encode($this->getParams($params)));
        return $this->render(
            'liqpay',
            [
                'formData' => [
                    'data' => $data,
                    'signature' => $this->getSignature($data),
                ],
                'order' => $order,
                'transaction' => $transaction,
            ]
        );
    }

    public function checkResult()
    {
        if (!isset($_POST['data'], $_POST['signature'])) {
            throw new BadRequestHttpException;
        }
        $data = Json::decode(base64_decode($_POST['data']));
        $transaction = $this->loadTransaction($data['order_id']);
        $transaction->result_data = Json::encode($_POST);
        $signature = $this->getSignature($_POST['data']);
        $transaction->status = ($_POST['signature'] == $signature && $data['status'] == 'success')
            ? OrderTransaction::TRANSACTION_SUCCESS
            : OrderTransaction::TRANSACTION_ERROR;
        if (!$transaction->save(true, ['status', 'result_data'])) {
            throw new HttpException(500);
        }
    }
}
