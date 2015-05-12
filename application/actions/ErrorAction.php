<?php


namespace app\actions;

use app\backend\components\BackendController;
use Yii;
use yii\web\ErrorAction as BaseErrorAction;

class ErrorAction extends BaseErrorAction
{
    public function run()
    {
        if (Yii::$app->response->is_backend === true) {
            $this->controller->layout = '@app/backend/views/layouts/main';
        }

        return parent::run();
    }
}