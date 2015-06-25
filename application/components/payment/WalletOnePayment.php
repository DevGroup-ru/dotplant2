<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;

class WalletOnePayment extends AbstractPayment
{
    protected $currency;
    protected $locale;
    protected $merchantId;
    protected $secretKey;

    /**
     * @param string $status
     * @param string $message
     */
    protected function echoResult($status, $message)
    {
        echo "WMI_RESULT=" . strtoupper($status) . "&";
        echo "WMI_DESCRIPTION=" . urlencode($message);
        \Yii::$app->end();
    }

    /**
     * @param OrderTransaction $transaction
     * @param integer $status
     */
    protected function saveTransactionStatus($transaction, $status)
    {
        $transaction->status = $status;
        if (!$transaction->save(true, ['status', 'result_data'])) {
            $this->echoResult("Retry", "Save error");
        }
    }

    public function content()
    {
        $formData = [
            'WMI_MERCHANT_ID' => $this->merchantId,
            'WMI_PAYMENT_AMOUNT' => $this->transaction->total_sum,
            'WMI_CURRENCY_ID' => $this->currency,
            'WMI_PAYMENT_NO' => $this->transaction->id,
            'WMI_DESCRIPTION' => 'BASE64:' . base64_encode('Order #' . $this->order->id),
        //        $formData["WMI_EXPIRED_DATE"] = "2019-12-31T23:59:59",
            'WMI_SUCCESS_URL' => Url::toRoute(['payment-success', 'id' => $this->order->id], true),
            'WMI_FAIL_URL' => Url::toRoute(['payment-error', 'id' => $this->order->id], true),
        ];
        if (!\Yii::$app->user->isGuest) {
            $formData['WMI_CUSTOMER_FIRSTNAME'] = \Yii::$app->user->identity->first_name;
            $formData['WMI_CUSTOMER_LASTNAME'] = \Yii::$app->user->identity->last_name;
            $formData['WMI_CUSTOMER_EMAIL'] = \Yii::$app->user->identity->email;
        }
        foreach ($formData as $name => $val) {
            if (is_array($val)) {
                usort($val, "strcasecmp");
                $formData[$name] = $val;
            }
        }
        uksort($formData, "strcasecmp");
        $fieldValues = "";
        foreach ($formData as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $v = iconv("utf-8", "windows-1251", $v);
                    $fieldValues .= $v;
                }
            } else {
                $value = iconv("utf-8", "windows-1251", $value);
                $fieldValues .= $value;
            }
        }
        $signature = base64_encode(pack("H*", md5($fieldValues . $this->secretKey)));
        $formData["WMI_SIGNATURE"] = $signature;
        return $this->render(
            'wallet-one',
            [
                'formData' => $formData,
                'order' => $this->order,
                'transaction' => $this->transaction,
            ]
        );
    }

    public function checkResult($hash = '')
    {
        if (!isset($_POST["WMI_SIGNATURE"], $_POST["WMI_PAYMENT_NO"], $_POST["WMI_ORDER_STATE"])) {
            $this->echoResult("Retry", "Bad request");
        }
        $data = [];
        foreach ($_POST as $key => $value) {
            if ($key !== "WMI_SIGNATURE") {
                $data[$key] = $value;
            }
        }
        uksort($data, "strcasecmp");
        $values = "";
        foreach ($data as $value) {
            $values .= iconv("utf-8", "windows-1251", $value);
        }
        $signature = base64_encode(pack("H*", md5($values . $this->secretKey)));
        $transaction = $this->loadTransaction($_POST["WMI_PAYMENT_NO"]);
        $transaction->result_data = Json::encode($_POST);
        if ($signature == $_POST["WMI_SIGNATURE"]) {
            if (strtoupper($_POST["WMI_ORDER_STATE"]) == "ACCEPTED") {
                $this->saveTransactionStatus($transaction, OrderTransaction::TRANSACTION_SUCCESS);
                $this->echoResult("Ok", "Order #" . $_POST["WMI_PAYMENT_NO"] . " has been paid");
            } else {
                $this->saveTransactionStatus($transaction, OrderTransaction::TRANSACTION_ERROR);
                $this->echoResult("Retry", "Unknown state ". $_POST["WMI_ORDER_STATE"]);
            }
        } else {
            $this->saveTransactionStatus($transaction, OrderTransaction::TRANSACTION_ERROR);
            $this->echoResult("Retry", "Bad signature " . $_POST["WMI_SIGNATURE"]);
        }
    }
}
