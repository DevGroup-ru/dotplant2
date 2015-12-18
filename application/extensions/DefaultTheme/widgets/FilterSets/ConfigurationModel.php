<?php

namespace app\extensions\DefaultTheme\widgets\FilterSets;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $viewFile = 'filter-sets';
    public $hideEmpty = true;
    public $usePjax = true;
    public $useNewFilter = false;

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            ['viewFile', 'string'],
            [['hideEmpty', 'usePjax', 'useNewFilter'], 'filter', 'filter' => 'boolval'],
        ];
    }
}
