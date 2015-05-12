<?php

namespace app\modules\image\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;
use yii\helpers\ArrayHelper;


/**
 * Class ConfigConfigurableModel represents configuration model for retrieving image input
 * in backend configuration subsystem.
 * @package app\modules\shop\models
 */
class ConfigConfigurableModel extends BaseConfigurationModel
{
    public $defaultThumbnailSize;
    public $noImageSrc;
    public $thumbnailsDirectory;
    public $useWatermark;
    public $watermarkDirectory;
    public $components = [];

    public function rules()
    {
        return [
            [['noImageSrc', 'defaultThumbnailSize', 'thumbnailsDirectory', 'watermarkDirectory'], 'string'],
            ['useWatermark', 'boolean'],
            ['components', 'required', 'isArray'],
        ];
    }

    public function isArray($attribute, $params)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, "The $attribute must be array");
        }
    }

    public function attributeLabels()
    {
        return [
            'defaultThumbnailSize' => Yii::t('app', 'Default thumbnail size'),
            'noImageSrc' => Yii::t('app', 'No image src'),
            'thumbnailsDirectory' => Yii::t('app', 'Thumbnails directory'),
            'useWatermark' => Yii::t('app', 'Use watermark'),
            'watermarkDirectory' => Yii::t('app', 'Watermark directory'),
            'components' => Yii::t('app', 'Components'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\image\ImageModule $module */
        $module = Yii::$app->getModule('image');
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
        $attributes = $this->attributes;
        unset($attributes['components']);
        $components = [];
        foreach ($this->components as $name => $component) {
            $necessary = ArrayHelper::getValue($component, 'necessary', []);
            $unnecessary = ArrayHelper::getValue($component, 'unnecessary', []);
            if (ArrayHelper::remove($necessary, 'active', false) === true) {
                foreach ($unnecessary as $confName => $confVal) {
                    if ($confVal === '') {
                        ArrayHelper::remove($unnecessary, $confName);
                    }
                }
                $components[$name] = ArrayHelper::merge($necessary, $unnecessary);
            }
        }
        return [
            'modules' => [
                'image' => $attributes,
            ],
            'components' => $components,
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
}
