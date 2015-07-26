<?php

namespace app\components\payment;

use app\modules\shop\models\Order;
use app\modules\shop\models\OrderTransaction;
use yii\base\Widget;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

abstract class AbstractPayment extends Widget
{
    /** @property Order $order*/
    public $order;
    /** @property OrderTransaction $transaction*/
    public $transaction;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->transaction instanceof OrderTransaction) {
            $this->transaction = new OrderTransaction();
        }
    }

    /**
     * @return string
     */
    abstract public function content();

    /**
     * @return mixed
     */
    abstract public function checkResult($hash = '');

    /**
     * @return string|null
     */
    public function customCheck()
    {
        return null;
    }

    /**
     * @param bool $success
     * @param integer|null $orderId
     */
//    protected function redirect($success, $orderId = null)
//    {
//        $url = ['/shop/payment/' . ($success ? 'success' : 'error')];
//        if (!is_null($orderId)) {
//            $url['id'] = $orderId;
//        }
//        \Yii::$app->response->redirect($url);
//    }

    /**
     * @param integer $id
     * @return OrderTransaction
     * @throws \yii\web\NotFoundHttpException
     */
    protected function loadTransaction($id)
    {
        if (null === $model = OrderTransaction::findOne($id)) {
            throw new NotFoundHttpException();
        }
        return $this->transaction = $model;
    }

    /**
     * @param array $params
     * @param bool|true $scheme
     * @return string
     */
    protected function createResultUrl(array $params = [], $scheme = true)
    {
        $result = [
            '/shop/payment/result',
            'othash' => $this->transaction->generateHash(),
        ];
        $params = array_merge($result, $params);
        return Url::toRoute($params, boolval($scheme));
    }

    /**
     * @param array $params
     * @param bool|true $scheme
     * @return string
     */
    protected function createErrorUrl(array $params = [], $scheme = true)
    {
        $result = [
            '/shop/payment/error',
            'othash' => $this->transaction->generateHash(),
        ];
        $params = array_merge($result, $params);
        return Url::toRoute($params, boolval($scheme));

    }

    /**
     * @param array $params
     * @param bool|true $scheme
     * @return string
     */
    protected function createSuccessUrl(array $params = [], $scheme = true)
    {
        $result = [
            '/shop/payment/success',
            'othash' => $this->transaction->generateHash(),
        ];
        $params = array_merge($result, $params);
        return Url::toRoute($params, boolval($scheme));

    }

    /**
     * @param array $params
     * @param bool|true $scheme
     * @return string
     */
    protected function createFailUrl(array $params = [], $scheme = true)
    {
        $result = [
            '/shop/payment/fail',
            'othash' => $this->transaction->generateHash(),
        ];
        $params = array_merge($result, $params);
        return Url::toRoute($params, boolval($scheme));

    }

    /**
     * @param array $params
     * @param bool|true $scheme
     * @return string
     */
    protected function createCancelUrl(array $params = [], $scheme = true)
    {
        $result = [
            '/shop/payment/cancel',
            'othash' => $this->transaction->generateHash(),
        ];
        $params = array_merge($result, $params);
        return Url::toRoute($params, boolval($scheme));
    }


    /**
     * @param string $url
     * @return \yii\web\Response
     */
    protected function redirect($url = '')
    {
        return \Yii::$app->response->redirect($url);
    }
}
?>