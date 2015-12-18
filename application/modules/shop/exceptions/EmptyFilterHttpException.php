<?php
namespace app\modules\shop\exceptions;

use app\modules\core\exceptions\CoreHttpException;

class EmptyFilterHttpException extends CoreHttpException
{
    public $view = '@app/modules/shop/views/exceptions/empty-filter.php';

    /**
     * @inheritdoc
     */
    public function __construct($status = 404, $message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($status, $message, $code, $previous);
    }
}