<?php

namespace app\components\payment;

use app\models\OrderTransaction;
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

    public function content($order, $transaction)
    {
        return $this->render(
            'interkassa',
            [
                'checkoutId' => $this->checkoutId,
                'currency' => $this->currency,
                'locale' => $this->locale,
                'order' => $order,
                'transaction' => $transaction,
            ]
        );
    }

    public function checkResult()
    {
        if (isset(
            $_GET['ik_co_id'],
            $_GET['ik_pm_no'],
            $_GET['ik_am'],
            $_GET['ik_cur'],
            $_GET['ik_act'],
            $_GET['ik_inv_id'],
            $_GET['ik_sign']
        )) {
            if ($this->getHash($_GET) == $_GET['ik_sign']) {
                $transaction = $this->loadTransaction($_GET['ik_pm_no']);
                $transaction->result_data = Json::encode($_GET);
                $transaction->status = $_GET['ik_inv_st'] == 'success'
                    ? OrderTransaction::TRANSACTION_SUCCESS
                    : OrderTransaction::TRANSACTION_ERROR;
                if (!$transaction->save(true, ['status', 'result_data'])) {
                    throw new HttpException(500);
                }
            }
        } else {
            throw new BadRequestHttpException;
        }
    }
}
