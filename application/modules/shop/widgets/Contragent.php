<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Contragent as ContragentModel;
use app\modules\shop\models\Customer as CustomerModel;
use yii\base\Widget;
use yii\widgets\ActiveForm;

class Contragent extends Widget
{
    public $viewFile = 'contragent/form';
    /** @var ContragentModel $model */
    public $model = null;
    /** @var CustomerModel $customer */
    public $customer = null;
    public $immutable = false;
    public $formAction = null;
    /** @var ActiveForm $form */
    public $form = null;
    public $additional = [];

    public function init()
    {
        parent::init();

        if ($this->customer instanceof CustomerModel && !$this->model instanceof ContragentModel) {
            $this->model = ContragentModel::createEmptyContragent($this->customer);
        } elseif (!$this->customer instanceof CustomerModel && $this->model instanceof ContragentModel) {
            $this->customer = $this->model->customer;
        }
    }

    public function run()
    {
        parent::run();

        if (!$this->model instanceof ContragentModel) {
            return null;
        }

        if ($this->immutable) {
            $this->model->setScenario('readonly');
            $this->model->getAbstractModel()->setScenario('readonly');
        }

        return $this->render($this->viewFile, [
            'model' => $this->model,
            'customer' => $this->customer,
            'immutable' => boolval($this->immutable),
            'action' => $this->formAction,
            'form' => $this->form,
            'additional' => $this->additional,
        ]);
    }
}
?>