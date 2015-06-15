<?php

namespace app\extensions\DefaultTheme\widgets\Navigation;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $rootNavigationId = 1;
    public $options = '{}';
    public $submenuTemplate = "\n<ul>\n{items}\n</ul>\n";

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            [
                [
                    'rootNavigationId',
                ],
                'integer',
            ],
            [
                [
                    'options',
                    'submenuTemplate',
                ],
                'string',
            ],
            [
                [
                    'rootNavigationId',
                ],
                'filter',
                'filter' => 'intval',
            ],
        ];
    }
}