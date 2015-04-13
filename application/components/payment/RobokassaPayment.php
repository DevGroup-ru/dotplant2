<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;

class RobokassaPayment extends AbstractPayment
{
    protected $merchantLogin;
    protected $merchantPass1;
    protected $merchantPass2;
    protected $merchantUrl;

    public function content($order, $transaction)
    {
        $invoiceDescription = urlencode(\Yii::t('app', 'Payment of order #{orderId}', ['orderId' => $order->id]));
        $inCurrency  = '';
        $culture = 'ru';
        $signatureValue  = md5(
            $this->merchantLogin . ':' . $transaction->total_sum . ':' . $transaction->id . ':' . $this->merchantPass1
        );
        $url = 'http://' . $this->merchantUrl . '/Index.aspx?MrchLogin=' . $this->merchantLogin
            . '&OutSum=' . $transaction->total_sum . '&InvId=' . $transaction->id . '&IncCurrLabel='
            . $inCurrency . '&Desc=' . $invoiceDescription . '&SignatureValue=' . $signatureValue
            . '&Culture=' . $culture . '&Encoding=utf-8';
        return $this->render(
            'robokassa',
            [
                'order' => $order,
                'url' => $url,
            ]
        );
    }

    public function checkResult()
    {
        if (!isset($_GET['OutSum'], $_GET['InvId'], $_GET['SignatureValue'])) {
            $this->redirect(false);
        }
        $signatureValue = md5($_GET['OutSum'] . ':' . $_GET['InvId'] . ':' . $this->merchantPass2);
        if ($_GET['SignatureValue'] != $signatureValue) {
            $this->redirect(false);
        }
        $transaction = $this->loadTransaction($_GET['InvId']);
        if ($_GET['OutSum'] != $transaction->total_sum) {
            $this->redirect(false, $transaction->order->id);
        }
        $transaction->result_data = Json::encode([$_GET['OutSum'], $_GET['InvId'], $_GET['SignatureValue']]);
        $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
        $this->redirect($transaction->save(true, ['status', 'result_data']), $transaction->order->id);
    }
}
