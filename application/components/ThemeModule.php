<?php

namespace app\components;

use Yii;

/**
 * Class ThemeModule is the base class that should be used by theme modules(themes).
 * @package app\components
 */
abstract class ThemeModule extends ExtensionModule
{
    /**
     * @var bool Should this theme be registered as @theme alias in config
     */
    public $registerThemeAlias = true;


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/views/configurable/_baseThemeConfig',
                'configurableModel' => 'app\models\BaseThemeConfigurationModel',
            ]
        ];
    }
}