<?php

namespace app\backend\events;

use Yii;
use yii\widgets\ActiveForm;
use yii\base\Model;

class BackendEntityEditFormEvent extends \yii\base\Event
{
    public $form;

    public $model;

    public function __construct(ActiveForm $form, Model &$model, $config = [])
    {
        parent::__construct($config);
        $this->form = $form;
        $this->model = $model;
    }

    public function getController()
    {
        return Yii::$app->controller;
    }

    public function getView()
    {
        return Yii::$app->controller->view;
    }
}