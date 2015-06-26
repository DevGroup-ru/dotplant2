<?php

namespace app\extensions\DefaultTheme\widgets\Navigation;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $rootNavigationId = 1;
    public $depth = 99;
    public $viewFile = 'navigation';
    public $options = '{}';
    public $linkTemplate = '<a href="{url}" title="{label}" itemprop="url"><span itemprop="name">{label}</span></a>';
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
                    'linkTemplate',
                    'submenuTemplate',
                    'viewFile',
                ],
                'string',
            ],
            [
                [
                    'rootNavigationId',
                    'depth',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [
                'depth',
                'integer',
                'min' => 1,
            ],
        ];
    }
}
