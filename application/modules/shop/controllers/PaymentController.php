<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\Order;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\PaymentType;
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

        return $this->render('cancel', ['transaction' => $transaction]);
    }
}
?>