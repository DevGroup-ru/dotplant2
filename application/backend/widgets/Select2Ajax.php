<?php

namespace app\backend\widgets;

use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\widgets\ActiveForm;

class Select2Ajax extends Widget
{
    public $viewFile = 'select2ajax/simple';
    /** @var ActiveForm $form */
    public $form = null;
    public $initialData = [];
    /** @var ActiveRecord $model */
    public $model = null;
    public $modelAttribute = null;
    /** @var bool $multiple */
    public $multiple = true;
    public $searchUrl = '';
    public $pluginOptions = [];
    public $additional = [];

    public function run()
    {
        parent::run();

        return $this->render($this->viewFile, [
            'form' => $this->form,
            'model' => $this->model,
            'modelAttribute' => $this->modelAttribute,
            'initialData' => $this->initialData,
            'multiple' => $this->multiple,
            'searchUrl' => $this->searchUrl,
            'pluginOptions' => $this->pluginOptions,
            'additional' => $this->additional,
        ]);
    }
}