<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class SpryPayPayment extends AbstractPayment
{
    protected $currency;
    protected $language;
    protected $secretKey;
    protected $shopId;

    public function content()
    {
        return $this->render(
            'sprypay',
            [
                'currency' => $this->currency,
                'language' => $this->language,
                'order' => $this->order,
                'shopId' => $this->shopId,
                'transaction' => $this->transaction,
            ]
        );
    }

    public function checkResult($hash = '')
    {
        $spQueryFields = [
            'spPaymentId', 'spShopId', 'spShopPaymentId', 'spBalanceAmount', 'spAmount', 'spCurrency',
            'spCustomerEmail', 'spPurpose', 'spPaymentSystemId', 'spPaymentSystemAmount', 'spPaymentSystemPaymentId',
            'spEnrollDateTime', 'spHashString', 'spBalanceCurrency'
        ];
        $hash = '';
        foreach ($spQueryFields as $spFieldName) {
            if (!isset($_POST[$spFieldName])) {
                throw new BadRequestHttpException;
            }
            if (!in_array($spFieldName, ['spHashString', 'spBalanceCurrency'])) {
                $hash .= $_POST[$spFieldName];
            }
        }
        $hash = md5($hash . $this->secretKey);
        $transaction = $this->loadTransaction($_POST['spShopPaymentId']);
        $transaction->result_data = Json::encode($_POST);
        if ($hash == $_POST['spHashString']) {
            $transaction->status =
                $this->currency == $_POST['spCurrency'] || $transaction->total_sum <= $_POST['spAmount']
                    ? OrderTransaction::TRANSACTION_SUCCESS
                    : OrderTransaction::TRANSACTION_ERROR;
            if (!$transaction->save(true, ['status', 'result_data'])) {
                throw new HttpException(500);
            }
            echo 'ok';
        } else {
            throw new BadRequestHttpException;
        }
    }
}
