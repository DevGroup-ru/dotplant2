<?php

namespace app\components\payment;

use app\models\Order;
use app\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class PayUPayment extends AbstractPayment
{
    protected $merchantName;
    protected $secretKey;
    protected $testMode;

    protected function getOrderHash($data)
    {
        $ignoredKeys = [
            'AUTOMODE',
            'BACK_REF',
            'DEBUG',
            'BILL_FNAME',
            'BILL_LNAME',
            'BILL_EMAIL',
            'BILL_PHONE',
            'BILL_ADDRESS',
            'BILL_CITY',
            'DELIVERY_FNAME',
            'DELIVERY_LNAME',
            'DELIVERY_PHONE',
            'DELIVERY_ADDRESS',
            'DELIVERY_CITY',
            'LU_ENABLE_TOKEN',
            'LU_TOKEN_TYPE',
            'TESTORDER',
        ];
        $hash = strlen($data['MERCHANT']) . $data['MERCHANT'];
        unset($data['MERCHANT']);
        foreach ($data as $key => $value) {
            if (in_array($key, $ignoredKeys)) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $productValue) {
                    $hash .= mb_strlen($productValue, '8bit') . $productValue;
                }
            } else {
                $hash .= mb_strlen($value, '8bit') . $value;
            }
        }
        return hash_hmac('md5', $hash, $this->secretKey);
    }

    /**
     * @param Order $order
     * @return array
     */
    protected function getFormData($order, $transaction)
    {
        $data = [
            'MERCHANT' => $this->merchantName,
            'ORDER_REF' => $transaction->id,
            'ORDER_DATE' => date('Y-m-d H:i:s'),
        ];
        foreach ($order->items as $item) {
            $data[] = [
                'ORDER_PNAME[]' => $item->product->name,
                'ORDER_PCODE[]' => $item->product_id,
                'ORDER_PRICE[]' => $item->product->price,
                'ORDER_QTY[]' => $item->quantity,
                'ORDER_VAT[]' => 0,
            ];
        }
        $data['ORDER_SHIPPING'] = $order->shippingOption->cost;
        $data['PRICES_CURRENCY'] = 'RUB';
        $data['BACK_REF'] = Url::toRoute(['/cart/payment-result', 'id' => $transaction->payment_type_id], true);
        $data['ORDER_HASH'] = $this->getOrderHash($data);
        if ($this->testMode) {
            $data['DEBUG'] = 'TRUE';
            $data['TESTORDER'] = 'TRUE';
        }
        $data['LANGUAGE'] = 'RU';
        return $data;
    }

    protected function getSignature($data = [])
    {
        $result = "";
        foreach ($data as $value) {
            if (is_array($value)) {
                foreach ($value as $piece) {
                    $result .= mb_strlen($piece, '8bit') . $piece;
                }
            } else {
                $result .= mb_strlen($value, '8bit') . $value;
            }
        }
        return hash_hmac("md5", $result, $this->secretKey);
    }

    public function content($order, $transaction)
    {
        $url = 'https://secure.payu.ru/order/lu.php';
        return $this->render(
            'payu',
            [
                'data' => $this->getFormData($order, $transaction),
                'order' => $order,
                'transaction' => $transaction,
                'url' => $url,
            ]
        );
    }

    public function checkResult()
    {
        if (isset($_GET['result'])) {
            if (in_array($_GET['result'], [-1, 0])) {
                $this->redirect(true);
            } else {
                $this->redirect(false);
            }
        }
        $requiredFields = ['IPN_PID', 'IPN_PNAME', 'IPN_DATE', 'ORDERSTATUS', 'HASH', 'REFNOEXT', 'ORDERSTATUS'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                throw new BadRequestHttpException;
            }
        }
        $hash = $_POST['HASH'];
        unset($_POST['HASH']);
        if ($this->getSignature($_POST) == $hash) {
            throw new BadRequestHttpException;
        }
        $transaction = OrderTransaction::findOne($_POST['REFNOEXT']);
        $transaction->result_data = Json::encode($_POST);
        if ($_POST['ORDERSTATUS'] == 'COMPLETE') {
            $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
        }
        if (!$transaction->save(true, ['status', 'result_data'])) {
            throw new HttpException(500);
        }
        $date = date('YmdHis');
        $returnHash = $this->getSignature(
            [
                'IPN_PID' => $_POST['IPN_PID'][0],
                'IPN_PNAME' => $_POST['IPN_PNAME'][0],
                'IPN_DATE' => $_POST['IPN_DATE'],
                'DATE' => $date,
            ]
        );
        echo '<EPAYMENT>' . $date . '|' . $returnHash . '</EPAYMENT>';
        \Yii::$app->end();
    }
}
