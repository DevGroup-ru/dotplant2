<?php

namespace app\extensions\DefaultTheme\widgets\PagesList;

use app\extensions\DefaultTheme\models\WidgetConfigurationModel;

class ConfigurationModel extends WidgetConfigurationModel
{
    public $parent_id = 1;
    public $limit = 5;

    public $more_pages_label = '';

    public $view_file = 'pages-list';
    public $order_by = 'date_added';
    public $order = SORT_DESC;
    public $display_date = false;
    public $date_format = 'd.m.Y';

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            [
                [
                    'display_date',
                ],
                'boolean',
            ],
            [
                [
                    'display_date',
                ],
                'filter',
                'filter'=>'boolval',
            ],
            [
                [
                    'limit',
                    'parent_id',
                    'order',
                ],
                'integer',
            ],
            [
                [
                    'limit',
                    'parent_id',
                    'order',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [
                [
                    'view_file',
                    'order_by',
                    'parent_id',
                ],
                'required',
            ],
            [
                [
                    'date_format',
                    'more_pages_label',
                ],
                'string',
            ],
        ];
    }
}