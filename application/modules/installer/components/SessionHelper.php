<?php

namespace app\modules\installer\components;


use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class SessionHelper extends Component
{
    protected $vars = [];

    public function get($id, $default = null)
    {
        if (YII_CONSOLE) {
            return ArrayHelper::getValue($this->vars, $id, $default);
        }
        return Yii::$app->session->get($id, $default);
    }

    public function set($id, $data)
    {
        if (YII_CONSOLE) {
            $this->vars[$id] = $data;
        } else {
            Yii::$app->session->set($id, $data);
        }
    }
}