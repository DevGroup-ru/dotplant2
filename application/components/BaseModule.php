<?php
namespace app\components;

use yii\base\Application;
use yii\base\Module;
use app\backend\BackendModule;
use app\backend\components\BackendController;

/**
 * BaseModule is the base module for core DotPlant2 modules.
 * Third-party modules & themes should extends yii\base\Module or something else.
 * @package app\components
 */
class BaseModule extends Module
{
    public $isCoreModule = true;

    public function getBackendGrids()
    {
        return [];
    }

    public function getBackendGridDefaultValue($key)
    {
        $grids = $this->getBackendGrids();
        foreach ($grids as $grid) {
            if ($key == $grid['key']) {
                return $grid['defaultValue'];
            }
        }
        return null;
    }

    /**
     * @param Application $app
     * @return bool
     */
    public function isFrontend(Application $app)
    {
        return false === $app->requestedAction->controller->module instanceof BackendModule
            && false === $app->requestedAction->controller instanceof BackendController;
    }
}
