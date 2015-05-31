<?php

namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

class RobokassaPayment extends AbstractPayment
{
    public $merchantLogin;
    public $merchantPass1;
    public $merchantPass2;
    public $merchantUrl;

    public function content()
    {
        $invoiceDescription = urlencode(\Yii::t('app', 'Payment of order #{orderId}', ['orderId' => $this->order->id]));
        $inCurrency  = '';
        $culture = 'ru';
        $signatureValue  = md5(
            $this->merchantLogin . ':' . $this->transaction->total_sum . ':' . $this->transaction->id . ':' . $this->merchantPass1
        );
        $url = 'http://' . $this->merchantUrl . '/Index.aspx?MrchLogin=' . $this->merchantLogin
            . '&OutSum=' . $this->transaction->total_sum . '&InvId=' . $this->transaction->id . '&IncCurrLabel='
            . $inCurrency . '&Desc=' . $invoiceDescription . '&SignatureValue=' . $signatureValue
            . '&Culture=' . $culture . '&Encoding=utf-8';
        return $this->render(
            'robokassa',
            [
                'order' => $this->order,
                'url' => $url,
            ]
        );
    }

    public function checkResult($hash = '')
    {
        $signatureValue = \Yii::$app->request->get('OutSum') . ':' . \Yii::$app->request->get('InvId') . ':' . $this->merchantPass2;
        if (md5($signatureValue) !== \Yii::$app->request->get('SignatureValue')) {
            throw new BadRequestHttpException();
        }
        if (null === $transaction = $this->loadTransaction(\Yii::$app->request->get('InvId'))) {
            throw new BadRequestHttpException();
        }
        if (\Yii::$app->request->get('OutSum') != $transaction->total_sum) {
            return $this->redirect($this->createErrorUrl(['id' => $transaction->id]));
        }
        $transaction->result_data = Json::encode([
            \Yii::$app->request->get('OutSum'),
            \Yii::$app->request->get('InvId'),
            \Yii::$app->request->get('SignatureValue')
        ]);
        $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
        if (!$transaction->save(true, ['status', 'result_data'])) {
            return $this->redirect($this->createErrorUrl(['id' => $transaction->id]));
        } else {
            return $this->redirect($this->createSuccessUrl(['id' => $transaction->id]));
        }
    }
}
?>