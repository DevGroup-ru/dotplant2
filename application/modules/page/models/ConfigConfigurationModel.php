<?php

namespace app\modules\page\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\modules\page\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    /**
     * @var int minimum pages per list to show
     */
    public $minPagesPerList = 1;

    /**
     * @var int maximum pages per list to show
     */
    public $maxPagesPerList = 50;

    /**
     * @var int pages per list to show
     */
    public $pagesPerList = 10;

    /**
     * @var int How much pages to show on search results page
     */
    public $searchResultsLimit = 10;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'minPagesPerList',
                    'maxPagesPerList',
                    'pagesPerList',
                    'searchResultsLimit',
                ],
                'integer',
                'min' => 1,
            ],
            [
                [
                    'minPagesPerList',
                    'maxPagesPerList',
                    'pagesPerList',
                    'searchResultsLimit',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [
                [
                    'minPagesPerList',
                    'maxPagesPerList',
                    'pagesPerList',
                    'searchResultsLimit',
                ],
                'required',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\page\PageModule $module */
        $module = Yii::$app->modules['page'];

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
                'page' => $attributes,
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
        return [
            '@article' => '/page/page/show',
            '@articles' => '/page/page/list',
            '@page' => '/page/page/show',
        ];
    }
}
