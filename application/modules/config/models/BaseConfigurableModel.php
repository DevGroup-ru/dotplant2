<?php

namespace app\modules\config\models;

use Yii;
use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * Abstract class for configurable models of configurable modules.
 * @package app\models
 */
abstract class BaseConfigurableModel extends Model
{
    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public abstract function webApplicationAttributes();

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public abstract function consoleApplicationAttributes();

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public abstract function commonApplicationAttributes();

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public abstract function keyValueAttributes();

    /**
     * The name of event that is triggered when this configuration is being saved.
     * The event will be triggered before model validation proceeds and after model is loaded with user-input.
     *
     * @return string Configuration save event name
     */
    public function configurationSaveEvent()
    {
        return StringHelper::basename(get_class($this)) . 'ConfigurationSaveEvent';
    }
}