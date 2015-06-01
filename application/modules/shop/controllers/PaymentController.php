<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\Order;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\PaymentType;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use Yii;

class PaymentController extends Controller
{
    /**
     * Check payment result by PaymentType id
     * @param null $id
     * @param null $paymentId
     * @param string $othash
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \yii\base\UnknownClassException
     */
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

        /**
         * Redirect on one of those methods
         */
        return $paymentType->getPayment()->checkResult($othash);
    }

    /**
     * Error message
     * @param null $id
     * @param null $othash
     * @return string
     * @throws BadRequestHttpException
     */
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

    /**
     * Success message
     * @param null $id
     * @param null $othash
     * @return string
     * @throws BadRequestHttpException
     */
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

    /**
     * Fail message
     * @param null $id
     * @param null $othash
     * @return string
     * @throws BadRequestHttpException
     */
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

    /**
     * Cancel transaction and delete it
     * @param null $id
     * @param null $othash
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws \Exception
     */
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
        if ($transaction->order->user_id === 0) {
            throw new ForbiddenHttpException;
        } else {
            if (Yii::$app->user->id !== $transaction->order->user_id) {
                throw new ForbiddenHttpException;
            }
        }

        $transaction->delete();
        return $this->redirect(Url::previous('__returnPaymentCancel'));
    }

    /**
     * Change PaymentType for OrderTransaction
     * @param null $id
     * @param null $othash
     * @param null $update
     * @return string
     * @throws BadRequestHttpException
     */
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
        if ($transaction->order->user_id === 0) {
            throw new ForbiddenHttpException;
        } else {
            if (Yii::$app->user->id !== $transaction->order->user_id) {
                throw new ForbiddenHttpException;
            }
        }

        if (\Yii::$app->request->isPost && null !== $update) {
            if ($transaction->load(\Yii::$app->request->post())) {
                $transaction->save();
            }
        }

        return $this->render('type', ['transaction' => $transaction]);
    }

//    /**
//     * Open OrderTransaction with rendering payment form
//     * @param null $id
//     * @param null $othash
//     * @return string
//     * @throws BadRequestHttpException
//     */
//    public function actionTransaction($id = null, $othash = null)
//    {
//        if (null === $id) {
//            throw new BadRequestHttpException();
//        }
//        /** @var OrderTransaction $transaction */
//        if (null === $transaction = OrderTransaction::findOne(['id' => $id])) {
//            throw new BadRequestHttpException();
//        }
//        if (!$transaction->checkHash($othash)) {
//            throw new BadRequestHttpException();
//        }
//
//        return $this->render('transaction', ['transaction' => $transaction]);
//    }
}
?>