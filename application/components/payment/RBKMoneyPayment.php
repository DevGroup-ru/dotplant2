<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;

class RBKMoneyPayment extends AbstractPayment
{
    public $eshopId;
    public $currency;
    public $language;
    public $secretKey;
    public $serviceName;

    public function content()
    {
        return $this->render(
            'rbk-money',
            [
                'currency' => $this->currency,
                'eshopId' => $this->eshopId,
                'language' => $this->language,
                'order' => $this->order,
                'serviceName' => $this->serviceName,
                'transaction' => $this->transaction,
            ]
        );
    }

    public function checkResult($hash = '')
    {
        if (isset($_GET['eshopId'], $_GET['orderId'], $_GET['serviceName'], $_GET['eshopAccount'],
            $_GET['paymentAmount'], $_GET['paymentCurrency'], $_GET['paymentStatus'], $_GET['userName'],
            $_GET['userEmail'], $_GET['paymentData'], $_GET['hash'])
        ) {
            $hash = md5(
                implode(
                    '::',
                    [
                        $_GET['eshopId'],
                        $_GET['orderId'],
                        $_GET['serviceName'],
                        $_GET['eshopAccount'],
                        $_GET['paymentAmount'],
                        $_GET['paymentCurrency'],
                        $_GET['paymentStatus'],
                        $_GET['userName'],
                        $_GET['userEmail'],
                        $_GET['paymentData'],
                        $this->secretKey,
                    ]
                )
            );
            if ($hash == $_GET['hash']) {
                $transaction = $this->loadTransaction($_GET['orderId']);
                $transaction->result_data = Json::encode($_GET);
                $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
                $this->redirect($transaction->save(true, ['result_data', 'status']), $transaction->order_id);
            }
        }
    }
}
