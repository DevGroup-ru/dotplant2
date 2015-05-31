<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class InterkassaPayment extends AbstractPayment
{
    protected $checkoutId;
    protected $currency;
    protected $locale;
    protected $secretKey;

    private function getHash($data)
    {
        unset($data['ik_sign']);
        foreach ($data as $key => $value) {
            if (strpos($key, 'ik_') !== 0) {
                unset($data[$key]);
            }
        }
        ksort($data, SORT_STRING);
        $data[] = $this->secretKey;
        return base64_encode(md5(implode(':', $data), true));
    }

    public function content()
    {
        return $this->render(
            'interkassa',
            [
                'checkoutId' => $this->checkoutId,
                'currency' => $this->currency,
                'locale' => $this->locale,
                'order' => $this->order,
                'transaction' => $this->transaction,
                'ik_suc_u' => $this->createResultUrl([
                    'id' => $this->order->payment_type_id,
                    'transactionId' => $this->transaction->id,
                ]),
                'ik_fal_u' => $this->createFailUrl([
                    'id' => $this->order->payment_type_id,
                ])
            ]
        );
    }

    public function checkResult($hash = '')
    {
        if (null === \Yii::$app->request->get('ik_sign')) {
            throw new BadRequestHttpException();
        }
        if ($this->getHash(\Yii::$app->request->get()) === \Yii::$app->request->get('ik_sign')) {
            if (null === $transaction = $this->loadTransaction(\Yii::$app->request->get('ik_pm_no'))) {
                throw new BadRequestHttpException();
            }
            $transaction->result_data = Json::encode(\Yii::$app->request->get());
            $transaction->status = 'success' === \Yii::$app->request->get('ik_inv_st')
                ? OrderTransaction::TRANSACTION_SUCCESS
                : OrderTransaction::TRANSACTION_ERROR;
            if (!$transaction->save(true, ['status', 'result_data'])) {
                throw new HttpException(500);
            } else {
                return ;
            }
        }
        throw new BadRequestHttpException();
    }
}
?>