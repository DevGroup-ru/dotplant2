<?php

namespace app\modules\image\models;

use app;
use app\modules\config\models\BaseConfigurableModel;
use Yii;

/**
 * Class ConfigConfigurableModel represents configuration model for retrieving image input
 * in backend configuration subsystem.
 * @package app\modules\shop\models
 */
class ConfigConfigurableModel extends BaseConfigurableModel
{
    public $defaultThumbnailSize;
    public $noImageSrc;
    public $thumbnailsDirectory;
    public $useWatermark;
    public $watermarkDirectory;

    public function rules()
    {
        return [
            [['noImageSrc', 'defaultThumbnailSize', 'thumbnailsDirectory', 'watermarkDirectory'], 'string'],
            ['useWatermark', 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'defaultThumbnailSize' => Yii::t('app', 'Default thumbnail size'),
            'noImageSrc' => Yii::t('app', 'No image src'),
            'thumbnailsDirectory' => Yii::t('app', 'Thumbnails directory'),
            'useWatermark' => Yii::t('app', 'Use watermark'),
            'watermarkDirectory' => Yii::t('app', 'Watermark directory'),
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
        return [
            'modules' => [
                'image' => $this->attributes,
            ],
            'components' => [
                'fs' => [
                    'class' => 'creocoder\flysystem\LocalFilesystem',
                    'path' => '@webroot/files',
                ],
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
}
