<?php

namespace app\modules\review;

use app\components\BaseModule;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\review
 */
class ReviewModule extends BaseModule
{
    public $maxPerPage = 10;
    public $pageSize = 10;

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
