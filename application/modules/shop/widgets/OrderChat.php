<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Order;
use yii\base\Widget;

class OrderChat extends Widget
{
    public $viewFile = 'order-chat/backend';
    /** @property \app\backend\models\OrderChat[]|array $list */
    public $list = [];
    /** @var Order $order */
    public $order = null;

    public function run()
    {
        parent::run();

        if (!$this->order instanceof Order) {
            return '';
        }

        return $this->render($this->viewFile, [
            'list' => $this->list,
            'order' => $this->order,
        ]);
    }
}
?>