<?php

namespace app\components\payment;

use app\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class PayOnlinePayment extends AbstractPayment
{
    protected $currency;
    protected $language;
    protected $merchantId;
    protected $privateKey;

    public function content($order, $transaction)
    {
        $params = 'MerchantId='. $this->merchantId;
        $params .= '&OrderId=' . $transaction->id;
        $params .= '&Amount=' . $transaction->total_sum;
        $params .= '&Currency=' . $this->currency;
        $params .= '&OrderDescription=Order #' . $order->id;
        $params .= '&PrivateSecurityKey=' . $this->privateKey;
        $hash = md5($params);
        $url = "https://secure.payonlinesystem.com/" . $this->language . "/payment/?MerchantId=" . $this->merchantId
            . "&OrderId=" . urlencode($transaction->id) . "&Amount=" . $transaction->total_sum
            . "&Currency=" . $this->currency . "&OrderDescription=" . urlencode('Order #' . $order->id)
            . "&ReturnUrl="
            . urlencode(Url::toRoute(['/cart/payment-result', 'id' => $transaction->payment_type_id], true))
            . "&FailUrl="
            . urlencode(Url::toRoute(['/cart/payment-error', 'id' => $order->id], true))
            . "&SecurityKey=" . $hash;
        return $this->render(
            'payonline',
            [
                'order' => $order,
                'transaction' => $transaction,
                'url' => $url,
            ]
        );
    }

    public function checkResult()
    {
        if (!isset($_POST['SecurityKey'], $_POST['TransactionID'], $_POST['OrderId'], $_POST['Amount'],
            $_POST['Currency'], $_POST['DateTime'])
        ) {
            throw new BadRequestHttpException;
        }
        $transaction = $this->loadTransaction($_POST['OrderId']);
        $transaction->result_data = Json::encode($_POST);
        $queryString = 'DateTime=' . $_POST['DateTime'] . '&TransactionID=' . $_POST['TransactionID']
            . '&OrderId=' . $_POST['OrderId'] . '&Amount=' . $_POST['Amount'] . '&Currency=' . $_POST['Currency']
            . '&PrivateSecurityKey='. $this->privateKey;
        if (md5($queryString) != $_POST['SecurityKey']) {
            $transaction->status = OrderTransaction::TRANSACTION_ERROR;
            if ($transaction->save(true, ['status', 'result_data'])) {
                throw new HttpException(500);
            }
            throw new BadRequestHttpException;
        }
        $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
        if ($transaction->save(true, ['status', 'result_data'])) {
            throw new HttpException(500);
        }
        $this->redirect(true, $transaction->order_id);
    }
}
