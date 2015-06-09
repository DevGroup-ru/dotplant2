<?php

namespace app\modules\core\decorators;

use app;
use app\components\Controller;
use Yii;

abstract class PreDecorator extends BaseDecorator
{

    /**
     * Adds handler for needed application/controller events
     * @param \yii\base\Application $app
     * @param \yii\base\Controller $controller
     * @return void
     */
    public function subscribe($app, $controller)
    {

        $controller->on(
            Controller::EVENT_PRE_DECORATOR,
            function ($event) use ($controller) {

                /** @var \app\modules\core\events\ViewEvent $event */
                $this->decorate(
                    $controller,
                    $event->viewFile,
                    $event->params
                );
            }
        );
    }

    /**
     * Handle decoration
     * @param $controller
     * @param $viewFile
     * @param $params
     * @return void
     */
    abstract public function decorate($controller, $viewFile, $params);
}