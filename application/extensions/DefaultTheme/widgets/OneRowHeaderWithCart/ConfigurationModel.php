<?php

namespace app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $collapseOnSmallScreen = true;

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            [
                [
                    'collapseOnSmallScreen',
                ],
                'boolean',
            ],
            [
                [
                    'collapseOnSmallScreen',
                ],
                'filter',
                'filter' => 'boolval',
            ],
        ];
    }
}