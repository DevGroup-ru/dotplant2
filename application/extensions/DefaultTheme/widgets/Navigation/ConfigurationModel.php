<?php

namespace app\extensions\DefaultTheme\widgets\Navigation;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $rootNavigationId = 1;


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
                    'rootNavigationId',
                ],
                'filter',
                'filter' => 'intval',
            ],
        ];
    }
}