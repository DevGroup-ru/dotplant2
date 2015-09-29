<?php

namespace app\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * Class AutoCompleteSearch
 * @package app\widgets
 * Example:
 *
 */
class AutoCompleteSearch extends \kartik\widgets\Typeahead
{
    /**
     * @var string|array the route to search action
     */
    public $route = '/default/auto-complete-search';
    public $template = '<a href="{{url}}">{{name}}</a>';

    public function init()
    {
        $url = is_array($this->route)
            ? Url::to($this->route)
            : Url::to([$this->route, 'term' => 'QUERY']);
        $this->dataset = [
            [
                'remote' => [
                    'url' => $url,
                    'wildcard'=> 'QUERY',
                ],
                'templates' => [
                    'empty' => Html::tag('span', Yii::t('app', 'Hit enter to search'), ['class'=>'empty-search']),
                    'suggestion' => new JsExpression("Handlebars.compile('{$this->template}')")
                ],
                'display' => 'name'
            ],
        ];

        $this->pluginOptions = [
            'hint' => false,
        ];
        parent::init();
    }

    public function run()
    {
        if (empty($this->dataset) || !is_array($this->dataset)) {
            throw new InvalidConfigException("You must define the 'dataset' property for Typeahead which must be an array.");
        }
        if (!is_array(current($this->dataset))) {
            throw new InvalidConfigException("The 'dataset' array must contain an array of datums. Invalid data found.");
        }
        $this->validateConfig();
        $this->initDataset();
        $this->registerAssets();
        $this->initOptions();
        echo $this->getInput('textInput');
    }
}
