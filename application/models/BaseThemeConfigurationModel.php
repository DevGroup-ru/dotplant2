<?php

namespace app\models;

use app;
use Yii;

class BaseThemeConfigurationModel extends app\modules\config\models\BaseConfigurationModel
{
    /**
     * @var bool Should this theme be registered as @theme alias in config
     */
    public $registerThemeAlias = true;

    /**
     * Fills model attributes with default values
     * @return void
     */
    public function defaultValues()
    {
        return;
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [
            'modules' => [
                $this->getModule() => [
                    'class' => $this->getModuleInstance()->className(),
                ],
            ]
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function keyValueAttributes()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     * @return array
     */
    public function aliases()
    {
        if ($this->registerThemeAlias === true) {
            if ($this->getModuleInstance() !== null) {
                $reflectionClass = new \ReflectionClass($this->getModuleInstance());
                return [
                    '@' . $this->getModule() => dirname($reflectionClass->getFileName()),
                ];
            }
        }

        return [];

    }
}