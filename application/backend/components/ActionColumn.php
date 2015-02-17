<?php

namespace app\backend\components;

use Closure;
use kartik\icons\Icon;
use Yii;
use yii\grid\Column;
use yii\helpers\Html;
use yii\helpers\Url;

class ActionColumn extends Column
{
    public $buttons = null;

    private $default_buttons = [
        [
            'url' => 'edit',
            'icon' => 'pencil',
            'class' => 'btn-primary',
            'label' => 'Edit',
        ],
        [
            'url' => 'clone',
            'icon' => 'copy',
            'class' => 'btn-success',
            'label' => 'Clone',
        ],
        [
            'url' => 'delete',
            'icon' => 'trash-o',
            'class' => 'btn-danger',
            'label' => 'Delete',
        ],
    ];

    private $callback_buttons = null;

    /**
     * @var string the ID of the controller that should handle the actions specified here.
     * If not set, it will use the currently active controller. This property is mainly used by
     * [[urlCreator]] to create URLs for different actions. The value of this property will be prefixed
     * to each action name to form the route of the action.
     */
    public $controller;
    /**
     * @var callable a callback that creates a button URL using the specified model information.
     * The signature of the callback should be the same as that of [[createUrl()]].
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public $urlCreator;

    public $url_append = '';

    public function init()
    {
        parent::init();

        if (null === $this->buttons) {
            $this->buttons = $this->default_buttons;
        } elseif ($this->buttons instanceof Closure) {
            $this->callback_buttons = $this->buttons;
        }
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     * @param string $action the button name (or action ID)
     * @param \yii\db\ActiveRecord $model the data model
     * @param mixed $key the key associated with the data model
     * @param integer $index the current row index
     * @return string the created URL
     */
    public function createUrl($action, $model, $key, $index)
    {
        if ($this->urlCreator instanceof Closure) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index);
        } else {
            $params = is_array($key) ? $key : ['id' => (string) $key];
            $params[0] = $this->controller ? $this->controller . '/' . $action : $action;

            return Url::toRoute($params).$this->url_append;
        }
    }


    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->callback_buttons instanceof Closure) {
            $_btns = call_user_func($this->callback_buttons, $model, $key, $index, $this);
            if (null === $_btns) {
                $this->buttons = $this->default_buttons;
            } else {
                $this->buttons = $_btns;
            }
        }
        $min_width = count($this->buttons) * 34; //34 is button-width
        $data = Html::beginTag('div', ['class' => 'btn-group', 'style'=>'min-width: '.$min_width.'px']);
        foreach ($this->buttons as $button) {
            Html::addCssClass($button, 'btn');
            Html::addCssClass($button, 'btn-sm');
            $buttonText = isset($button['text']) ? ' ' . $button['text'] : '';
            $data .= Html::a(
                Icon::show($button['icon']) . $buttonText,
                $url = $this->createUrl($button['url'], $model, $key, $index),
                [
                    'data-pjax' => 0,
                    'class' => $button['class'],
                    'data-action' => $button['url'],
                    'title' => $button['label'],
                ]
            ) . ' ';
        }
        $data .= '</div>';

        return $data;
    }
}
