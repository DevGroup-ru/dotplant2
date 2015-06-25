<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class Pay2PayPayment extends AbstractPayment
{
    protected $currency;
    protected $hiddenKey;
    protected $language;
    protected $merchantId;
    protected $secretKey;
    protected $testMode;

    public function content()
    {
        $data = [
            'version' => '1.2',
            'merchant_id' => $this->merchantId,
            'language' => $this->language,
            'order_id' => $this->transaction->id,
            'amount' => $this->transaction->total_sum,
            'currency' => $this->currency,
            'description' => 'Order #' . $this->order->id,
            'success_url' => Url::toRoute(['/cart/payment-success', 'id' => $this->order->id], true),
            'fail_url' => Url::toRoute(['/cart/payment-error', 'id' => $this->order->id], true),
            'result_url' => Url::toRoute(['/cart/payment-result', 'id' => $this->transaction->payment_type_id], true),
            'test_mode' => $this->testMode,
        ];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<request>';
        foreach ($data as $key => $value) {
            $xml .= Html::tag($key, $value);
        }
        $xml .= '</request>';
        $sign = md5($this->secretKey . $xml . $this->secretKey);
        $xmlEncode = base64_encode($xml);
        $signEncode = base64_encode($sign);
        return $this->render(
            'pay2pay',
            [
                'order' => $this->order,
                'signEncode' => $signEncode,
                'transaction' => $this->transaction,
                'xmlEncode' => $xmlEncode,
            ]
        );
    }

    public function checkResult($hash = '')
    {
        if (isset($_POST['xml'], $_POST['sign'])) {
            $xml = base64_decode(str_replace(' ', '+', $_POST['xml']));
            $sign = base64_decode(str_replace(' ', '+', $_POST['sign']));
            $data = simplexml_load_string($xml);
            if ($data->order_id) {
                $transaction = $this->loadTransaction($data->order_id);
                $transaction->result_data = Json::encode($_POST);
                $generatedSign = md5($this->hiddenKey . $xml . $this->hiddenKey);
                if ($generatedSign == $sign) {
                    $currency = strtoupper($data->currency_code_iso);
                    if ($currency == 'RUR') {
                        $currency = 'RUB';
                    }
                    if (strtoupper($this->currency) !== $currency || $transaction->total_sum < $data->amount
                        || !in_array($data->status, ['success', 'fail'])
                    ) {
                        throw new BadRequestHttpException;
                    }
                    if ($data->status == 'success') {
                        $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
                    } else {
                        $transaction->status = OrderTransaction::TRANSACTION_ERROR;
                    }
                    if ($transaction->save(true, ['status', 'result_data'])) {
                        echo '<?xml version="1.0" encoding="UTF-8"?>';
                        echo '<result>';
                        echo '<status>yes</status>';
                        echo '<err_msg></err_msg>';
                        echo '</result>';
                    } else {
                        throw new HttpException(500);
                    }
                } else {
                    throw new BadRequestHttpException;
                }
            }
        }
    }
}
