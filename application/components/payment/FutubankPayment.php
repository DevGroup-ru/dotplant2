<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class FutubankPayment extends AbstractPayment
{
    protected $currency;
    protected $merchant;
    protected $secretKey;
    protected $testing;

    /**
     * @param $data
     * @return string
     */
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

    /**
     * @param int $length
     * @return string
     */
    private function generateSalt($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $result;
    }

    /**
     * @return string
     */
    public function content()
    {
        $formData = [
            'client_email' => '',
            'client_name' => '',
            'client_phone' => '',
            'merchant' => $this->merchant,
            'unix_timestamp' => time(),
            'salt' => $this->generateSalt(32),
            'amount' => $this->transaction->total_sum,
            'currency' => $this->currency,
            'description' => 'Order #' . $this->order->id,
            'order_id' => $this->transaction->id,
            'success_url' => $this->createSuccessUrl(['id' => $this->transaction->id, 'hash' => $this->transaction->generateHash()]),
            'fail_url' => $this->createFailUrl(['id' => $this->transaction->id, 'hash' => $this->transaction->generateHash()]),
            'cancel_url' => $this->createCancelUrl(['id' => $this->transaction->id, 'hash' => $this->transaction->generateHash()]),
            'meta' => Json::encode(['transactionId' => $this->transaction->id]),
        ];
        if ($this->testing == 1) {
            $formData['testing'] = 1;
        }
        $formData['signature'] = $this->getSignature($formData);
        return $this->render(
            'futubank',
            [
                'formData' => $formData,
                'order' => $this->order,
                'transaction' => $this->transaction,
            ]
        );
    }

    /**
     * @param string $hash
     * @return string
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function checkResult($hash = '')
    {
        $transactionId = \Yii::$app->request->post('order_id');
        if (null === $transactionId) {
            throw new BadRequestHttpException();
        }
        if (null === $model = $this->loadTransaction($transactionId)) {
            throw new BadRequestHttpException();
        }

        $model->result_data = Json::encode(\Yii::$app->request->post());
        if ($this->getSignature(\Yii::$app->request->post()) === \Yii::$app->request->post('signature')) {
            $model->status = OrderTransaction::TRANSACTION_SUCCESS;
            if ($model->save(true, ['status', 'result_data'])) {
                return 'OK ' . $model->id;
            } else {
                throw new ServerErrorHttpException();
            }
        } else {
            $model->status = OrderTransaction::TRANSACTION_ERROR;
            $model->save(true, ['status', 'result_data']);
            throw new BadRequestHttpException();
        }
    }
}