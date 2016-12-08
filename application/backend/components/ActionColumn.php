<?php

namespace app\backend\components;

use Closure;
use kartik\icons\Icon;
use Yii;
use yii\grid\Column;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\backend\components\Helper;

class ActionColumn extends Column
{
    public $buttons;

    private $defaultButtons = [];

    private $callbackButtons;

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

    public $appendReturnUrl = true;

    public function init()
    {
        parent::init();

        $this->defaultButtons = [
            [
                'url' => 'edit',
                'icon' => 'pencil',
                'class' => 'btn-primary',
                'label' => Yii::t('app', 'Edit'),
            ],
            [
                'url' => 'delete',
                'icon' => 'trash-o',
                'class' => 'btn-danger',
                'label' => Yii::t('app', 'Delete'),
                'options' => [
                    'data-action' => 'delete',
                ],
            ]
        ];


        if (null === $this->buttons) {
            $this->buttons = $this->defaultButtons;
        } elseif ($this->buttons instanceof Closure) {
            $this->callbackButtons = $this->buttons;
        }
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     * @param string $action the button name (or action ID)
     * @param \yii\db\ActiveRecord $model the data model
     * @param mixed $key the key associated with the data model
     * @param integer $index the current row index
     * @param bool $appendReturnUrl custom return url for each button
     * @param string $url_append custom append url for each button
     * @param string $keyParam custom param if $key is string
     * @param array $attrs list of model attributes used in route params
     * @return string the created URL
     */
    public function createUrl(
        $action,
        $model,
        $key,
        $index,
        $appendReturnUrl = null,
        $url_append = null,
        $keyParam = 'id',
        $attrs = []
    ) {
        if ($this->urlCreator instanceof Closure) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index);
        } else {
            $params = [];
            if (is_array($key)) {
                $params = $key;
            } else {
                if (is_null($keyParam) === false) {
                    $params = [$keyParam => (string)$key];
                }
            }
            $params[0] = $this->controller ? $this->controller . '/' . $action : $action;
            foreach ($attrs as $attrName) {
                if ($attrName === 'model') {
                    $params['model'] = $model;
                } elseif ($attrName === 'mainCategory.category_group_id' && $model->getMainCategory()) {
                    $params['category_group_id'] = $model->getMainCategory()->category_group_id;
                } else {
                    $params[$attrName] = $model->getAttribute($attrName);
                }
            }
            if (is_null($appendReturnUrl) === true) {
                $appendReturnUrl = $this->appendReturnUrl;
            }
            if (is_null($url_append) === true) {
                $url_append = $this->url_append;
            }
            if ($appendReturnUrl) {
                $params['returnUrl'] = Helper::getReturnUrl();
            }
            return Url::toRoute($params) . $url_append;
        }
    }


    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->callbackButtons instanceof Closure) {
            $btns = call_user_func($this->callbackButtons, $model, $key, $index, $this);
            if (null === $btns) {
                $this->buttons = $this->defaultButtons;
            } else {
                $this->buttons = $btns;
            }
        }
        $min_width = count($this->buttons) * 34; //34 is button-width
        $data = Html::beginTag('div', ['class' => 'btn-group', 'style' => 'min-width: ' . $min_width . 'px']);
        foreach ($this->buttons as $button) {
            $appendReturnUrl = ArrayHelper::getValue($button, 'appendReturnUrl', $this->appendReturnUrl);
            $url_append = ArrayHelper::getValue($button, 'url_append', $this->url_append);
            $keyParam = ArrayHelper::getValue($button, 'keyParam', 'id');
            $attrs = ArrayHelper::getValue($button, 'attrs', []);
            Html::addCssClass($button, 'btn');
            Html::addCssClass($button, 'btn-sm');
            $buttonText = isset($button['text']) ? ' ' . $button['text'] : '';
            $data .= Html::a(
                    Icon::show($button['icon']) . $buttonText,
                    $url = $this->createUrl(
                        $button['url'],
                        $model,
                        $key,
                        $index,
                        $appendReturnUrl,
                        $url_append,
                        $keyParam,
                        $attrs
                    ),
                    ArrayHelper::merge(
                        isset($button['options']) ? $button['options'] : [],
                        [
                            'class' => $button['class'],
                            'title' => $button['label'],
                        ]
                    )
                ) . ' ';
        }
        $data .= '</div>';
        return $data;
    }
}
