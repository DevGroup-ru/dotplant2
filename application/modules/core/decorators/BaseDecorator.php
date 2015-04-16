<?php

namespace app\modules\core\decorators;

use app;
use Yii;

/**
 * BaseDecorator is the base class for post&pre decorators
 * @package app\modules\core\decorators
 */
abstract class BaseDecorator
{
    /**
     * Adds handler for needed application/controller events
     * @param \yii\base\Application $app
     * @param \yii\base\Controller $controller
     * @return void
     */
    abstract public function subscribe($app, $controller);
}