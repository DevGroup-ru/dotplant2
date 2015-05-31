<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

class IntellectMoneyPayment extends AbstractPayment
{
    protected $eshopId;
    protected $currency;
    protected $language;
    protected $secretKey;
    protected $serviceName;

    public function content()
    {
        return $this->render(
            'intellect-money',
            [
                'currency' => $this->currency,
                'eshopId' => $this->eshopId,
                'language' => $this->language,
                'order' => $this->order,
                'serviceName' => $this->serviceName,
                'transaction' => $this->transaction,
                'successUrl' => $this->createResultUrl([
                    'id' => $this->order->payment_type_id,
                    'transactionId' => $this->transaction->id,
                ]),
                'failUrl' => $this->createFailUrl([
                    'id' => $this->transaction->id,
                ]),
            ]
        );
    }

    public function checkResult($hash = '')
    {
        $check = [
            \Yii::$app->request->get('eshopId'),
            \Yii::$app->request->get('orderId'),
            \Yii::$app->request->get('serviceName'),
            \Yii::$app->request->get('eshopAccount'),
            \Yii::$app->request->get('recipientAmount'),
            \Yii::$app->request->get('recipientCurrency'),
            \Yii::$app->request->get('paymentStatus'),
            \Yii::$app->request->get('userName'),
            \Yii::$app->request->get('userEmail'),
            \Yii::$app->request->get('paymentData'),
            $this->secretKey,
        ];
        $check = strtoupper(md5(implode('::', $check)));
        if ($check === strtoupper(\Yii::$app->request->get('hash'))) {
            if (null === $transaction = $this->loadTransaction(\Yii::$app->request->get('orderId'))) {
                throw new BadRequestHttpException();
            }
            $transaction->result_data = Json::encode(\Yii::$app->request->get());
            $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
            if ($transaction->save(true, ['result_data', 'status'])) {
                return $this->redirect($this->createSuccessUrl(['id' => $transaction->id]));
            } else {
                return $this->redirect($this->createErrorUrl(['id' => $transaction->id]));
            }
        }

        throw new BadRequestHttpException();
    }
}
?>