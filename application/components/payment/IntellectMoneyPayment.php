<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;

class IntellectMoneyPayment extends AbstractPayment
{
    protected $eshopId;
    protected $currency;
    protected $language;
    protected $secretKey;
    protected $serviceName;

    public function content($order, $transaction)
    {
        return $this->render(
            'intellect-money',
            [
                'currency' => $this->currency,
                'eshopId' => $this->eshopId,
                'language' => $this->language,
                'order' => $order,
                'serviceName' => $this->serviceName,
                'transaction' => $transaction,
            ]
        );
    }

    public function checkResult()
    {
        if (isset($_GET['eshopId'], $_GET['orderId'], $_GET['serviceName'], $_GET['eshopAccount'],
        $_GET['recipientAmount'], $_GET['recipientCurrency'], $_GET['paymentStatus'], $_GET['userName'],
        $_GET['userEmail'], $_GET['paymentData'], $_GET['hash'])
        ) {
            $hash = strtoupper(
                md5(
                    implode(
                        '::',
                        [
                            $_GET['eshopId'],
                            $_GET['orderId'],
                            $_GET['serviceName'],
                            $_GET['eshopAccount'],
                            $_GET['recipientAmount'],
                            $_GET['recipientCurrency'],
                            $_GET['paymentStatus'],
                            $_GET['userName'],
                            $_GET['userEmail'],
                            $_GET['paymentData'],
                            $this->secretKey,
                        ]
                    )
                )
            );
            if ($hash == strtoupper($_GET['hash'])) {
                $transaction = $this->loadTransaction($_GET['orderId']);
                $transaction->result_data = Json::encode($_GET);
                $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
                $this->redirect($transaction->save(true, ['result_data', 'status']), $transaction->order_id);
            }
        }
    }
}
