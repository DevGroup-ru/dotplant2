<?php

namespace app\extensions\DefaultTheme\widgets\CategoriesList;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $rootCategoryId = 1;
    public $categoryGroupId = 1;
    public $type = 'plain';
    public $activeClass = '';
    public $activateParents = false;

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
                'type',
                'in',
                'range' => ['plain', 'tree']
            ],
            [
                [
                    'rootCategoryId',
                    'categoryGroupId',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [['activeClass'], 'string'],
            [['activateParents'], 'boolean'],
        ];
    }
}