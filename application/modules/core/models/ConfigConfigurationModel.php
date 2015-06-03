<?php

namespace app\modules\core\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\modules\shop\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    /**
     * @var string Path to composer home directory(ie. /home/user/.composer/)
     */
    public $composerHomeDirectory = './composer/';

    /**
     * @var string Internal encoding. It's used for mbstring functions.
     */
    public $internalEncoding;

    public $autoCompleteResultsCount = 5;

    public $fileUploadPath = 'upload/user-uploads/';

    public $spamCheckerApiKey;

    public $serverName = 'localhost';

    public $daysToStoreSubmissions;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'composerHomeDirectory',
                    'internalEncoding',
                    'fileUploadPath',
                    'serverName',
                ],
                'required',
            ],
            [
                [
                    'autoCompleteResultsCount',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [
                [
                    'autoCompleteResultsCount',
                    'daysToStoreSubmissions',
                ],
                'integer',
            ],
            [
                [
                    'spamCheckerApiKey',
                ],
                'string',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\shop\ShopModule $module */
        $module = $this->getModuleInstance();

        $attributes = array_keys($this->getAttributes(null, ['composerHomeDirectory']));
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
        $attributes = $this->getAttributes(null, ['composerHomeDirectory']);
        return [
            'modules' => [
                'core' => $attributes,
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
        return [
            'components' => [
                'updateHelper' => [
                    'composerHomeDirectory' => $this->composerHomeDirectory,
                ]
            ],
        ];
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
            '@core' => dirname(__FILE__) . '/../',
        ];
    }
}