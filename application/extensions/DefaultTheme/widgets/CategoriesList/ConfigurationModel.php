<?php

namespace app\extensions\DefaultTheme\widgets\CategoriesList;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $rootCategoryId = 1;

    public $categoryGroupId = 1;

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            [
                [
                    'rootCategoryId',
                    'categoryGroupId',
                ],
                'integer',
            ],
            [
                [
                    'rootCategoryId',
                    'categoryGroupId',
                ],
                'filter',
                'filter' => 'intval',
            ],
        ];
    }
}