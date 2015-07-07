<?php

namespace app\modules\review;

use app\components\BaseModule;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\review
 */
class ReviewModule extends BaseModule
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

    /**
     * @return array the behavior configurations.
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/review/views/configurable/_config',
                'configurableModel' => 'app\modules\review\models\ConfigConfigurationModel',
            ]
        ];
    }
}
