<?php

namespace app\modules\review\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 * @package app\modules\review\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    /**
     * @var int Max reviews on page
     */
    public $maxPerPage = 10;

    /**
     * @var int Default number of reviews on page
     */
    public $pageSize = 10;

    /**
     * @var bool Enable spam checking
     */
    public $enableSpamChecking = false;

    public function attributeLabels()
    {
        return [
            'maxPerPage' => Yii::t('app', 'Max per page'),
            'pageSize' => Yii::t('app', 'Page size'),
            'enableSpamChecking' => Yii::t('app', 'Enable spam checking'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['maxPerPage', 'pageSize'], 'integer'],
            [['enableSpamChecking'], 'filter', 'filter' => 'boolval'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\review\reviewModule $module */
        $module = Yii::$app->getModule('review');
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
        $attributes = $this->getAttributes();
        return [
            'modules' => [
                'review' => $attributes,
            ],
        ];
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
        return [];
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
