<?php

namespace app\modules\seo\models;

use app\modules\config\models\BaseConfigurationModel;
use app\modules\seo\SeoModule;
use Yii;

class ConfigConfigurationModel extends BaseConfigurationModel
{
    public $cacheConfig;
    public $include;
    public $mainPage;
    public $redirectWWW = SeoModule::NO_REDIRECT;
    public $redirectTrailingSlash = false;
    public $analytics;

    public function attributeLabels()
    {
        return [
            'mainPage' => Yii::t('app', 'Main page'),
            'include' => Yii::t('app', 'Includes'),
            'redirectWWW' => Yii::t('app', 'Redirect WWW'),
            'redirectTrailingSlash' => Yii::t('app', 'Redirect trailing slash'),
        ];
    }

    public function rules()
    {
        return [
            [['mainPage', 'include', 'cacheConfig', 'redirectWWW', 'redirectTrailingSlash', 'analytics'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var SeoModule $module */
        $module = Yii::$app->modules['seo'];
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
        return [
            'modules' => [
                'seo' => $this->attributes,
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
     * @inheritdoc
     */
    public function aliases()
    {
        return [];
    }
}
