<?php

namespace app\extensions\DefaultTheme;

use app\components\ThemeModule;

/**
 * Default Theme module
 * @package app\extensions\DefaultTheme
 */
class Module extends ThemeModule
{
    public $controllerMap = [
        'backend-configuration' => 'app\extensions\DefaultTheme\backend\ConfigurationController',
    ];

    /**
     * @return string Returns class name for theme component that should extend app\components\Theme
     */
    public function themeClassName()
    {
        return 'app\extensions\DefaultTheme\components\Theme';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/extensions/DefaultTheme/views/configurable/_config',
                'configurableModel' => 'app\extensions\DefaultTheme\models\ConfigurationModel',
            ]
        ];
    }
}