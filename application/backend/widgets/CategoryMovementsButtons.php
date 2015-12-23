<?php

namespace app\backend\widgets;


use yii\base\Widget;
use Yii;
use yii\helpers\Html;
use yii\base\InvalidParamException;
use app\modules\shop\models\Category;
use kartik\icons\Icon;

class CategoryMovementsButtons extends Widget
{
    const MOVE_ACTION = 'move-selected';
    const ADD_ACTION = 'add-selected';
    public $url;
    public $gridSelector;
    public $wrapperClass = '';
    public $addText = '';
    public $moveText = '';
    /**
     * @var array here you can define buttons classes like this:
     * [
     *  'add-class' => 'btn btn-danger', //class for add button
     *  'move-class' => 'btn btn-danger', //class for move button
     * ]
     *
     */
    public $htmlOptions = [];
    private $modalSelector = '#mass-categories-actions';
    private static $categories = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->url, $this->gridSelector)) {
            throw new InvalidParamException('Attribute \'url\' or \'gridSelector\' is not set');
        }
        if (true === empty($this->moveText)) {
            $this->moveText = Yii::t('app', 'Are you sure you want to move all selected products into');
        }
        if (true === empty($this->addText)) {
            $this->addText = Yii::t('app', 'Are you sure you want to add all selected products into');
        }
        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = '';
        }
        if (true === empty(static::$categories)) {
            $cc = Category::find()
                ->select('id, name')
                ->where(['active' => 1])
                ->asArray(true)
                ->all();
            foreach ($cc as $k => $cat) {
                static::$categories[$cat['id']] = $cat['name'];
            }
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->registerScript();
        return $this->renderButtons();
    }

    /**
     * @return string
     */
    protected function renderButtons()
    {
        $buttons = [
            self::ADD_ACTION => Html::button(
                Icon::show('plus') . ' ' .
                Yii::t('app', 'Add selected to:'),
                [
                    'class' => isset($this->htmlOptions['add-class']) ?
                        $this->htmlOptions['add-class'] : 'btn btn-default',
                    'disabled' => 'disabled',
                    'data-mc-action' => self::ADD_ACTION
                ]),
            self::MOVE_ACTION => Html::button(
                Icon::show('arrows') . ' ' .
                Yii::t('app', 'Move selected to:'),
                [
                    'class' => isset($this->htmlOptions['move-class']) ?
                        $this->htmlOptions['move-class'] : 'btn btn-default',
                    'disabled' => 'disabled',
                    'data-mc-action' => self::MOVE_ACTION
                ]),

        ];
        $group = '';
        foreach ($buttons as $id => $button) {
            $group .= Html::tag('div',
                Html::tag('div',
                    $button . "\n\t" . Html::tag('div',
                        Html::dropDownList(null, null, static::$categories, [
                            'prompt' => Yii::t('app', 'Select category'),
                            'class' => 'form-control',
                            'id' => $id,
                        ]), [
                            'class' => 'input-group',
                        ]), [
                        'class' => 'btn-group',
                    ]), [
                    'class' => 'col-xs-12 col-sm-6',
                ]);
        };
        return Html::tag('div', $group, ['class' => 'row m-bottom-10']);
    }

    /**
     *
     */
    protected function registerScript()
    {
        $JS = <<<JS
            $("#%1\$s, #%2\$s").change(function (){
                var \$this = $(this),
                    buttonSelector = \$this.attr('id'),
                    \$button = $('button[data-mc-action="' + buttonSelector + '"]'),
                    \$selected = $('option:selected', \$this),
                    categoryId = parseInt(\$selected.val()),
                    categoryName = \$selected.text(),
                    items =  $("{$this->gridSelector}").yiiGridView('getSelectedRows');
                if (items.length) {
                    if (false === isNaN(categoryId)) {
                        \$button.attr("disabled", false);
                        \$button.data('cat-id', categoryId)
                            .data('cat-name', categoryName)
                            .data('items', items);
                    } else {
                        \$button.attr("disabled", true);
                    }
                }
            });
            $('button[data-mc-action]').click(function () {
                var \$this = $(this),
                    \$modal = $("{$this->modalSelector}"),
                     action = \$this.data('mc-action'),
                    \$textContainer = $('#mass-categories-action-modal-text', \$modal),
                    categoryId = parseInt(\$this.data('cat-id')),
                    categoryName = \$this.data('cat-name'),
                    items = \$this.data('items');
                    \$textContainer.closest('.alert').removeClass('alert-danger').addClass('alert-info');
                    if (false === isNaN(categoryId)) {
                        switch (action) {
                            case '%1\$s' :
                                \$textContainer.text('{$this->addText} ' + categoryName + '?');
                                break;
                            case '%2\$s' :
                                \$textContainer.text('{$this->moveText} ' + categoryName + '?');
                                break;
                        }
                        \$modal.data('url', "{$this->url}")
                            .data('items', items)
                            .data('cat-id', categoryId)
                            .data('mc-action', action)
                            .modal('show');
                    }
                return false;
            });
JS;
        $this->view->registerJs(sprintf($JS, self::ADD_ACTION, self::MOVE_ACTION));
    }
}