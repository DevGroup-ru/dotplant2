<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class LiqPayPayment extends AbstractPayment
{
    protected $supportedCurrencies = ['EUR','UAH','USD','RUB','RUR'];
    protected $currency;
    protected $language;
    protected $privateKey;
    protected $publicKey;
    protected $testMode;

    /**
     * @param $params
     * @return mixed
     */
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

    /**
     * @param $str
     * @return string
     */
    protected function getSignature($str)
    {
        return base64_encode(sha1($this->privateKey . $str . $this->privateKey, 1));
    }

    /**
     * @return string
     */
    public function content()
    {
        $params = [
            'version' => 3,
            'amount' => $this->transaction->total_sum,
            'currency' => $this->currency,
            'description' => "Order #" . $this->order->id,
            'order_id' => $this->transaction->id,
            'result_url' => $this->createSuccessUrl([
                'id' => $this->transaction->id,
            ]),
            'server_url' => $this->createResultUrl([
                'id' => $this->transaction->payment_type_id
            ]),
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
                'order' => $this->order,
                'transaction' => $this->transaction,
            ]
        );
    }

    /**
     * @param string $hash
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\NotFoundHttpException
     * @return void
     */
    public function checkResult($hash = '')
    {
        try {
            $data = Json::decode(base64_decode(\Yii::$app->request->post('data', '')));
        } catch (InvalidParamException $e) {
            $data = ['status' => ''];
        }
        $signature = \Yii::$app->request->post('signature');
        $calcSignature = $this->getSignature(\Yii::$app->request->post('data'));
        if ($signature !== $calcSignature) {
            throw new BadRequestHttpException();
        }

        if (null === $transaction = $this->loadTransaction($data['order_id'])) {
            throw new BadRequestHttpException();
        }

        $transaction->status = 'success' === $data['status']
            ? OrderTransaction::TRANSACTION_SUCCESS
            : OrderTransaction::TRANSACTION_ERROR;
        $transaction->result_data = Json::encode(\Yii::$app->request->post());
        if (!$transaction->save(true, ['status', 'result_data'])) {
            throw new ServerErrorHttpException();
        } else {
            return ;
        }
    }
}