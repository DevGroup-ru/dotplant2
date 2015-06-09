<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\DeliveryInformation;
use app\modules\shop\models\OrderDeliveryInformation;
use yii\base\Widget;
use yii\widgets\ActiveForm;

class Delivery extends Widget
{
    public $viewFile = 'delivery/form';
    /** @var DeliveryInformation $deliveryInformation */
    public $deliveryInformation = null;
    /** @var OrderDeliveryInformation $orderDeliveryInformation */
    public $orderDeliveryInformation = null;
    public $immutable = false;
    public $formAction = null;
    /** @var ActiveForm $form */
    public $form = null;
    public $additional = [];

    public function run()
    {
        parent::run();

        if (!$this->deliveryInformation instanceof DeliveryInformation && !$this->orderDeliveryInformation instanceof OrderDeliveryInformation) {
            return '';
        }

        if ($this->immutable) {
            if (null !== $this->orderDeliveryInformation) {
                $this->orderDeliveryInformation->setScenario('readonly');
                $this->orderDeliveryInformation->getAbstractModel()->setScenario('readonly');
            }
        }

        return $this->render($this->viewFile, [
            'deliveryInformation' => $this->deliveryInformation,
            'orderDeliveryInformation' => $this->orderDeliveryInformation,
            'immutable' => boolval($this->immutable),
            'action' => $this->formAction,
            'form' => $this->form,
            'additional' => $this->additional,
        ]);
    }
}
?>