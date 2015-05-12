<?php

namespace app\modules\data\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurableModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 * @package app\modules\data\models
 */
class ConfigConfigurableModel extends BaseConfigurationModel
{

    /**
     * @var string path to export dir
     */
    public $exportDirPath;

    /***
     * @var string path to import dir
     */
    public $importDirPath;

    /**
     * @var string default type of export
     */
    public $defaultType;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['exportDirPath', 'exportDirPath'], 'required'],
            [['defaultType'], 'in', 'range' => array_keys(\app\modules\data\models\ImportModel::knownTypes())]
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\data\ImportModel $module */
        $module = Yii::$app->modules['data'];

        $attributes = array_keys($this->getAttributes());
        foreach ($attributes as $attribute) {
            $this->{$attribute} = $module->{$attribute};
        }
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
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
     * @return array
     */
    public function commonApplicationAttributes()
    {
        $attributes = $this->getAttributes();
        return [
            'modules' => [
                'data' => $attributes,
            ],
        ];
    }

    /**
     * Returns array of key=>values for configuration.
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
        return [];
    }
}