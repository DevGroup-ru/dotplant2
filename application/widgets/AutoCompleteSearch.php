<?php

namespace app\widgets;

use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * Class AutoCompleteSearch
 * @package app\widgets
 * Example:
 *
 */
class AutoCompleteSearch extends InputWidget
{
    private $widgetParams;
    /**
     * @var array of options list for jui.autocomplete
     */
    public $clientOptions;
    /**
     * @var string the result ul class name
     */
    public $listClass;
    /**
     * @var string|array the route to search action
     */
    public $route = '/default/auto-complete-search';

    public function init()
    {
        parent::init();
        $this->options['id'] = $this->id;
        $this->widgetParams = [
            'attribute' => $this->attribute,
            'clientOptions' => is_array($this->clientOptions) ? $this->clientOptions : [],
            'model' => $this->model,
            'name' => $this->name,
            'options' => $this->options,
            'value' => $this->value,
        ];
        if (!is_null($this->route)) {
            $this->widgetParams['clientOptions']['source'] = Url::to($this->route);
        }
    }

    public function run()
    {
        parent::run();
        return $this->render(
            'auto-complete-search',
            [
                'listClass' => $this->listClass,
                'widgetParams' => $this->widgetParams,
            ]
        );
    }
}
