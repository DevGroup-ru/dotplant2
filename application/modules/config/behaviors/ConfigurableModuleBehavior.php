<?php

namespace app\modules\config\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Module;
use yii\helpers\StringHelper;

/**
 * Behavior for DotPlant2 modules for configuration support
 * @package app\behaviors
 */
class ConfigurableModuleBehavior extends Behavior
{
    /**
     * @var string Returns Model name, that should handle configuration of module
     */
    public $configurableModel = null;

    /**
     * @var string Returns path to configuration view
     */
    public $configurationView = null;

    /**
     * @var array Stores config values array
     */
    private $configValues = null;

    /**
     * return array events for module
     */
    public function events()
    {
        return [
            Module::EVENT_BEFORE_ACTION => 'preloadConfigValues',
        ];
    }

    /**
     * Preloads configuration values from php files that stores php array
     */
    public function preloadConfigValues()
    {
        if ($this->configValues === null) {
            $ownerName = StringHelper::basename(get_class($this->owner));

            if (isset(Yii::$app->params['kv-'.$ownerName]) === true) {
                $this->configValues = Yii::$app->params['kv-'.$ownerName];
            } else {
                // config is empty for now
                $this->configValues = [];
            }
        }
    }

    /**
     * Returns key-value config value
     * @param string $key
     * @param null|mixed $defaultValue
     */
    public function getConfigValue($key, $defaultValue = null)
    {
        $this->preloadConfigValues();
        if (isset($this->configValues[$key]) === true) {
            return $this->configValues;
        } else {
            return $defaultValue;
        }
    }
}