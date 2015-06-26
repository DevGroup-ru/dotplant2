<?php

namespace app\extensions\DefaultTheme\widgets\FilterSets;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $viewFile = 'filter-sets';
    public $hideEmpty = true;

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            ['viewFile', 'string'],
            ['hideEmpty', 'filter', 'filter' => 'boolval'],
        ];
    }
}
