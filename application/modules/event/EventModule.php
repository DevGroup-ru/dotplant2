<?php

namespace app\modules\event;


use app\components\BaseModule;
use app\modules\event\interfaces\EventInterface;
use yii\base\Application;
use yii\base\BootstrapInterface;


/**
 * Class EventModule
 * @package app\modules\event
 *
 * This module provide ability to attach custom handlers to any existing event from any module, even if module itself
 * will not be bootstrapped
 */
class EventModule extends BaseModule implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        foreach ($app->modules as $module) {
            $class = null;
            switch (gettype($module)) {
                case "string":
                    $class = $module;
                    break;

                case "array":
                    if (isset($module["class"])) {
                        $class = $module["class"];
                    }
                    break;

                case "object":
                    $class = get_class($module);
                    break;
            }

            if (
                is_string($class)
                && class_exists($class, true)
                && in_array(EventInterface::class, class_implements($class, true))
            ) {
                $class::attachEventsHandlers();
            }
        }
    }
}