<?php

namespace app\modules\page;

use app;
use app\components\BaseModule;
use Yii;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\page
 */
class PageModule extends BaseModule
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
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/page/views/configurable/_config',
                'configurableModel' => 'app\modules\page\models\ConfigConfigurableModel',
            ]
        ];
    }
}