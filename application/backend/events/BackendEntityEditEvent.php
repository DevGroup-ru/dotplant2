<?php

namespace app\backend\events;

use app;
use Yii;
use yii\base\Model;

class BackendEntityEditEvent extends \yii\base\Event {
    /**
     * @var bool whether to continue running the action
     */
    public $isValid = true;

    public $modelClassName;

    public $model;


    public function __construct(Model &$model, $config=[])
    {
        parent::__construct($config);
        $this->model = $model;
        $this->modelClassName = $this->model->className();

    }
}