<?php

namespace app\modules\page;

use app;
use Yii;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\page
 */
class PageModule extends app\components\BaseModule
{
    const BACKEND_PAGE_GRID = 'pageGrid';
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

    public $controllerMap = [
        'backend' => 'app\modules\page\backend\PageController',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/page/views/configurable/_config',
                'configurableModel' => 'app\modules\page\models\ConfigConfigurationModel',
            ]
        ];
    }

    /** @inheritdoc */
    public function getBackendGrids()
    {
        return [
            [
                'defaultValue' => app\backend\BackendModule::BACKEND_GRID_ONE_TO_ONE,
                'key' => self::BACKEND_PAGE_GRID,
                'label' => Yii::t('app', 'Page edit'),
            ],
        ];
    }
}
