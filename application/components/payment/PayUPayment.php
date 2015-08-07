<?php

namespace app\components\payment;

use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Response;

class PayUPayment extends AbstractPayment
{
    public $merchantId = '';
    public $merchantSecretKey = '';
    public $systemTestMode = false;
    public $systemDebugMode = false;
    public $systemUrl_LU = '';

    /**
     * @return string
     */
    public function content()
    {
        $formData = $this->getData_LU();
        return $this->render(
            'payu',
            [
                'data' => $formData,
                'order' => $this->order,
                'transaction' => $this->transaction,
                'url' => $this->systemUrl_LU,
            ]
        );
    }

    /**
     * @param string $hash
     * @throws BadRequestHttpException
     * @throws HttpException
     * @throws \yii\base\ExitException
     */
    public function checkResult($hash = '')
    {
    }

    /**
     * @return string
     */
    public function customCheck()
    {
        return $this->checkHash_IPN();
    }

    /**
     * @return array
     */
    protected function getData_LU()
    {
        $result = [
            'MERCHANT' => $this->merchantId,
            'ORDER_REF' => $this->transaction->id, // $this->order->id,
            'ORDER_DATE' => date('Y-m-d H:i:s'),
            'ORDER_PNAME[]' => [],
            'ORDER_PCODE[]' => [],
            'ORDER_PINFO[]' => [],
            'ORDER_PRICE[]' => [],
            'ORDER_QTY[]' => [],
            'ORDER_VAT[]' => [],
            'ORDER_SHIPPING' => null !== $this->order->shippingOption ? $this->order->shippingOption->cost : 0,
            'PRICES_CURRENCY' => null !== Currency::getMainCurrency() ? Currency::getMainCurrency()->iso_code : 'RUB',
            'ORDER_PRICE_TYPE[]' => [],
        ];

        /** @var OrderItem $item */
        foreach ($this->order->items as $item) {
            $product = $item->product;
            $result['ORDER_PNAME[]'][] = $product->name;
            $result['ORDER_PINFO[]'][] = $product->name;
            $result['ORDER_PCODE[]'][] = $product->id;
            $result['ORDER_PRICE[]'][] = $product->convertedPrice();
            $result['ORDER_QTY[]'][] = $item->quantity;
            $result['ORDER_VAT[]'][] = 0;
            $result['ORDER_PRICE_TYPE[]'][] = 'NET';
        }

        $result['ORDER_HASH'] = $this->generateHash_LU($result);
        $result['LANGUAGE'] = 'RU';
        if (true === $this->systemTestMode) {
            $result['TESTORDER'] = 'TRUE';
            $result['DEBUG'] = true === $this->systemDebugMode ? 'TRUE' : 'FALSE';
        }

        return $result;
    }

    /**
     * @param array $input
     * @return string
     */
    protected function generateHash_LU($input)
    {
        $f = function ($input) use (&$f) {
            return array_reduce($input, function ($result, $item) use (&$f) {
                $result .= is_array($item) ? $f($item) : mb_strlen($item, '8bit') . $item;
                return $result;
            }, '');
        };
        $hash = $f($input);

        return hash_hmac(
            'md5',
            $hash,
            $this->merchantSecretKey
        );
    }

    protected function checkHash_IPN()
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;

        $requiredFields = ['REFNOEXT', 'IPN_PID', 'IPN_PNAME', 'IPN_DATE', 'ORDERSTATUS', 'HASH'];
        $post = \Yii::$app->request->post();
        foreach ($requiredFields as $r) {
            if (empty($post[$r])) {
                throw new BadRequestHttpException();
            }
        }

        $inputHash = mb_strtolower($post['HASH'], '8bit');
        unset($post['HASH']);

        if ($inputHash !== $this->generateHash_LU($post)) {
            throw new BadRequestHttpException();
        }

        $dt = date('YmdHis');
        $outputHash = $this->generateHash_LU([
            'IPN_PID' => $post['IPN_PID'][0],
            'IPN_PNAME' => $post['IPN_PNAME'][0],
            'IPN_DATE' => $post['IPN_DATE'],
            'DATE' => $dt
        ]);

        $transactionId = $post['REFNOEXT'];
        $transaction = $this->loadTransaction($transactionId);
            $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
        $transaction->save();

        return sprintf('<EPAYMENT>%s|%s</EPAYMENT>', $dt, $outputHash);
    }
}
