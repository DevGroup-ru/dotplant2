<?php

namespace app\backgroundtasks\models;

use app;
use app\modules\config\models\BaseConfigurationModel;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\backgroundtasks\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    public $daysToStoreNotify = 28;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['daysToStoreNotify'], 'integer', 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\shop\ShopModule $module */
        $module = $this->getModuleInstance();

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
        $attributes = $this->getAttributes();
        return [
            'modules' => [
                'background' => $attributes,
            ],
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
        return [
            'modules' => [
                'background' => [
                    'daysToStoreNotify' => $this->daysToStoreNotify,
                ]
            ]
        ];
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
        return [
            '@shop' => dirname(__FILE__) . '/../',
        ];
    }
}