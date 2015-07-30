<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class PayOnlinePayment extends AbstractPayment
{
    protected $currency;
    protected $language;
    protected $merchantId;
    protected $privateKey;

    /**
     * @return string
     */
    public function content()
    {
        $params = 'MerchantId='. $this->merchantId;
        $params .= '&OrderId=' . $this->transaction->id;
        $params .= '&Amount=' . $this->transaction->total_sum;
        $params .= '&Currency=' . $this->currency;
        $params .= '&OrderDescription=Order #' . $this->order->id;
        $params .= '&PrivateSecurityKey=' . $this->privateKey;
        $hash = md5($params);
        $url = "https://secure.payonlinesystem.com/" . $this->language . "/payment/?MerchantId=" . $this->merchantId
            . "&OrderId=" . urlencode($this->transaction->id) . "&Amount=" . $this->transaction->total_sum
            . "&Currency=" . $this->currency . "&OrderDescription=" . urlencode('Order #' . $this->order->id)
            . "&ReturnUrl="
            . urlencode($this->createResultUrl([
                'id' => $this->order->payment_type_id,
                'transactionId' => $this->transaction->id,
            ]))
            . "&FailUrl="
            . urlencode($this->createFailUrl(['id' => $this->transaction->id]))
            . "&SecurityKey=" . $hash;
        return $this->render(
            'payonline',
            [
                'order' => $this->order,
                'transaction' => $this->transaction,
                'url' => $url,
            ]
        );
    }

    /**
     * @param string $hash
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function checkResult($hash = '')
    {
        $queryString = 'DateTime=' . \Yii::$app->request->post('DateTime')
            . '&TransactionID=' . \Yii::$app->request->post('TransactionID')
            . '&OrderId=' . \Yii::$app->request->post('OrderId')
            . '&Amount=' . \Yii::$app->request->post('Amount')
            . '&Currency=' . \Yii::$app->request->post('Currency')
            . '&PrivateSecurityKey='. $this->privateKey;

        if (\Yii::$app->request->post('SecurityKey') !== md5($queryString)) {
            throw new BadRequestHttpException();
        }
        if (null === $transaction = $this->loadTransaction(\Yii::$app->request->post('OrderId'))) {
            throw new BadRequestHttpException();
        }
        $transaction->result_data = Json::encode(\Yii::$app->request->post());
        $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
        if (!$transaction->save(true, ['status', 'result_data'])) {
            throw new ServerErrorHttpException();
        }
        return $this->redirect($this->createSuccessUrl([
            'id' => $transaction->id,
        ]));
    }
}