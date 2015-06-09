<?php

namespace app\modules\installer\components;

use Yii;
use yii\web\Session;

class StaticSession extends Session
{
    public $vars = [];
    public function getUseCustomStorage()
    {
        return true;
    }

    public function readSession($id)
    {
        return $this->vars;
    }

    public function writeSession($id, $data)
    {
        $this->vars = $data;
    }
}