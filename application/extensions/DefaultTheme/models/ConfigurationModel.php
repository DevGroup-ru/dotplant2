<?php

namespace app\extensions\DefaultTheme\models;

use app;
use Yii;
use yii\helpers\ArrayHelper;

class ConfigurationModel extends app\models\BaseThemeConfigurationModel
{


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * Fills model attributes with default values
     * @return void
     */
    public function defaultValues()
    {
        /** @var app\extensions\DefaultTheme\Module $module */
        $module = Yii::$app->modules['DefaultTheme'];

        $attributes = array_keys($this->getAttributes());
        foreach ($attributes as $attribute) {
            $this->{$attribute} = $module->{$attribute};
        }
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
                $this->getModule() => ArrayHelper::merge(
                    [
                        'class' => $this->getModuleInstance()->className(),
                    ],
                    $this->getAttributes()
                )
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