<?php

namespace app\components;

use yii\base\Module;

/**
 * BaseModule is the base module for core DotPlant2 modules.
 * Third-party modules & themes should extends yii\base\Module or something else.
 * @package app\components
 */
class BaseModule extends Module
{
    public $isCoreModule = true;
}