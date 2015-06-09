<?php

namespace app\backend\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\backend\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    /**
     * @var boolean Should we show backend floating panel on bottom?
     */
    public $floatingPanelBottom = false;

    public $wysiwygUploadDir = '/upload/images';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'floatingPanelBottom',
                ],
                'boolean',
            ],
            [
                [
                    'floatingPanelBottom',
                ],
                'filter',
                'filter' => 'boolval',
            ],
            [
                [
                    'wysiwygUploadDir',
                ],
                'required',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'floatingPanelBottom' => Yii::t('app', 'Display backend floating panel on bottom'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\backend\BackendModule $module */
        $module = Yii::$app->modules['backend'];

        $attributes = array_keys($this->getAttributes());
        foreach ($attributes as $attribute) {
            if ($module->hasProperty($attribute)) {
                $this->{$attribute} = $module->{$attribute};
            }
        }
        $this->floatingPanelBottom = false;
        if (is_array($module->floatingPanel)) {
            if (isset($module->floatingPanel['bottom'])) {
                $this->floatingPanelBottom = $module->floatingPanel['bottom'];
            }
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
        $attributes = $this->getAttributes(null, ['floatingPanelBottom']);
        $attributes['floatingPanel'] = [
            'bottom' => $this->floatingPanelBottom,
        ];

        return [
            'modules' => [
                'backend' => $attributes,
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
        return [];
    }
}