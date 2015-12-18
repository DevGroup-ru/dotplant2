<?php
namespace app\modules\core\exceptions;

use yii\web\HttpException;

class CoreHttpException extends HttpException
{
    public $view = '';

    /**
     * @inheritdoc
     */
    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($status, $message, $code, $previous);
    }
}