<?php

namespace app\extensions\DefaultTheme\widgets\CategoriesList;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $rootCategoryId = 1;
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
                ],
                'filter',
                'filter' => 'intval',
            ],
            [['activeClass'], 'string'],
            [['activateParents'], 'boolean'],
        ];
    }
}