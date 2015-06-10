<?php

namespace app\extensions\DefaultTheme\widgets\ContentBlock;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $key = '';


    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            [
                [
                    'key',
                ],
                'required',
            ],
        ];
    }
}