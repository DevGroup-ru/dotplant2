<?php


namespace app\actions;

use app\modules\core\exceptions\CoreHttpException;
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
        $exception = Yii::$app->errorHandler->exception;

        if (Yii::$app->response->is_backend === true && Yii::$app->user->isGuest === false) {
            $this->controller->layout = '@app/backend/views/layouts/main';
        }

        if ($exception instanceof CoreHttpException) {
            /** @var CoreHttpException $exception */
            $this->view = $exception->view;
        }

        return parent::run();
    }
}