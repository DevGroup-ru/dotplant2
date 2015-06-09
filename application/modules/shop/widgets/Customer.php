<?php

namespace app\modules\shop\widgets;

use \app\modules\shop\models\Customer as CustomerModel;
use yii\base\Widget;
use yii\widgets\ActiveForm;

class Customer extends Widget
{
    public $viewFile = 'customer/form';
    /** @var CustomerModel $model */
    public $model = null;
    public $immutable = false;
    public $formAction = null;
    /** @var ActiveForm $form */
    public $form = null;
    public $additional = [];

    public function run()
    {
        parent::run();

        if (!$this->model instanceof CustomerModel) {
            return '';
        }

        if ($this->immutable) {
            $this->model->setScenario('readonly');
            $this->model->getAbstractModel()->setScenario('readonly');
        }

        return $this->render($this->viewFile, [
            'model' => $this->model,
            'immutable' => boolval($this->immutable),
            'action' => $this->formAction,
            'form' => $this->form,
            'additional' => $this->additional,
        ]);
    }
}
?>