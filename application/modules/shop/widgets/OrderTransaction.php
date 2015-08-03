<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Order;
use yii\base\Widget;
use yii\helpers\Url;

class OrderTransaction extends Widget
{
    public $viewFile = 'order-transaction/list';
    /** @var Order $model */
    public $model = null;
    public $immutable = false;
    public $returnPaymentCancel = null;
    public $additional = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();

        if (null === $this->returnPaymentCancel) {
            $this->returnPaymentCancel = \Yii::$app->request->getUrl();
        }
        Url::remember($this->returnPaymentCancel, '__returnPaymentCancel');

        return $this->render($this->viewFile, [
            'model' => $this->model,
            'immutable' => boolval($this->immutable),
            'additional' => $this->additional,
        ]);
    }
}
?>