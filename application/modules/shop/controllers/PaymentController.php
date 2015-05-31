<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\Order;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\PaymentType;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class PaymentController extends Controller
{
    public function actionResult($id = null, $paymentId = null, $othash = '')
    {
        $id = null === $id ? $paymentId : $id;
        if (null === $id) {
            throw new BadRequestHttpException();
        }

        /** @var PaymentType $paymentType */
        if (null === $paymentType = PaymentType::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }

        return $paymentType->getPayment()->checkResult($othash);
    }

    public function actionError($id = null, $othash = null)
    {
        if (null === $id) {
            throw new BadRequestHttpException();
        }
        /** @var OrderTransaction $transaction */
        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }
        if (!$transaction->checkHash($othash)) {
            throw new BadRequestHttpException();
        }

        return $this->render('error', ['transaction' => $transaction]);
    }

    public function actionSuccess($id = null, $othash = null)
    {
        if (null === $id) {
            throw new BadRequestHttpException();
        }
        /** @var OrderTransaction $transaction */
        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }
        if (!$transaction->checkHash($othash)) {
            throw new BadRequestHttpException();
        }

        return $this->render('success', ['transaction' => $transaction]);
    }

    public function actionFail($id = null, $othash = null)
    {
        if (null === $id) {
            throw new BadRequestHttpException();
        }
        /** @var OrderTransaction $transaction */
        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }
        if (!$transaction->checkHash($othash)) {
            throw new BadRequestHttpException();
        }

        return $this->render('fail', ['transaction' => $transaction]);
    }

    public function actionCancel($id = null, $othash = null)
    {
        if (null === $id) {
            throw new BadRequestHttpException();
        }
        /** @var OrderTransaction $transaction */
        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }
        if (!$transaction->checkHash($othash)) {
            throw new BadRequestHttpException();
        }
        if ($transaction->order->getImmutability(Order::IMMUTABLE_USER)) {
            throw new BadRequestHttpException();
        }

        $transaction->delete();
        return $this->redirect(Url::previous('__returnPaymentCancel'));
    }

    public function actionType($id = null, $othash = null, $update = null)
    {
        if (null === $id) {
            throw new BadRequestHttpException();
        }
        /** @var OrderTransaction $transaction */
        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }
        if (!$transaction->checkHash($othash)) {
            throw new BadRequestHttpException();
        }
        if ($transaction->order->getImmutability(Order::IMMUTABLE_USER)) {
            throw new BadRequestHttpException();
        }

        if (\Yii::$app->request->isPost && null !== $update) {
            if ($transaction->load(\Yii::$app->request->post())) {
                $transaction->save();
            }
        }

        return $this->render('type', ['transaction' => $transaction]);
    }

    public function actionTransaction($id = null, $othash = null)
    {
        if (null === $id) {
            throw new BadRequestHttpException();
        }
        /** @var OrderTransaction $transaction */
        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
            throw new BadRequestHttpException();
        }
        if (!$transaction->checkHash($othash)) {
            throw new BadRequestHttpException();
        }

        return $this->render('transaction', ['transaction' => $transaction]);
    }

}
?>