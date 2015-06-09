<?php

namespace app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart;

use Yii;

class ExpandableSearchField extends \yii\base\Widget
{
    public $autocomplete = true;
    public $useFontAwesome = true;
    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function run()
    {
        return $this->render(
            'expandable-search-field',
            [
                'autocomplete' => $this->autocomplete,
                'useFontAwesome' => $this->useFontAwesome,
            ]
        );
    }
}