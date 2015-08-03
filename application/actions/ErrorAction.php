<?php


namespace app\actions;

use Yii;
use yii\web\ErrorAction as BaseErrorAction;

class ErrorAction extends BaseErrorAction
{
    /**
     * @inheritdoc
     * @return string
     */
    public function run()
    {
        if (Yii::$app->response->is_backend === true && Yii::$app->user->isGuest === false) {
            $this->controller->layout = '@app/backend/views/layouts/main';
        }

        return parent::run();
    }
}