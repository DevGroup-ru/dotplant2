<?php

namespace app\backend\widgets;

use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\Html;
use kartik\icons\Icon;
use Yii;

class PublishSwitchButtons extends Widget
{
    const MASS_PUBLISH = 'publish';
    const MASS_UNPUBLISH = 'unpublish';

    public $url;
    public $gridSelector;
    public $wrapperClass = '';
    public $publishText = '';
    public $unpublishText = '';
    /**
     * @var array here you can define buttons classes like this:
     * [
     *  'on-class' => 'btn btn-danger', //class for publish all button
     *  'off-class' => 'btn btn-danger', //class for unpublish all button
     * ]
     *
     */
    public $htmlOptions = [];
    public $modalSelector = '#mass-publish-confirmation';

    public function init()
    {
        if (!isset($this->url, $this->gridSelector)) {
            throw new InvalidParamException('Attribute \'url\' or \'gridSelector\' is not set');
        }
        if (true === empty($this->publishText)) {
            $this->publishText = Yii::t('app', 'Are you sure you want to publish all selected items?');
        }
        if (true === empty($this->unpublishText)) {
            $this->unpublishText = Yii::t('app', 'Are you sure you want to unpublish all selected items?');
        }
        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = 'publishSwitch';
        }
    }

    public function run()
    {
        $this->registerScript();
        return $this->renderButtons();
    }

    protected function renderButtons()
    {
        $buttons = Html::button(
                Icon::show('eye') . ' ' .
                \Yii::t('app', 'Publish All'),
                [
                    'class' => isset($this->htmlOptions['on-class']) ?
                        $this->htmlOptions['on-class'] : 'btn btn-default',
                    'data-action' => self::MASS_PUBLISH
                ]

            )
            . Html::button(
                Icon::show('eye-slash') . ' ' .
                \Yii::t('app', 'Unpublish All'),
                [
                    'class' => isset($this->htmlOptions['on-class'])
                        ? $this->htmlOptions['on-class'] : 'btn btn-default',
                    'data-action' => self::MASS_UNPUBLISH
                ]
            );
        return Html::tag('div', $buttons, [
            'id' => $this->htmlOptions['id'],
            'class' => 'btn-group ' . $this->wrapperClass,
            'role' => 'group',
        ]);
    }

    protected function registerScript()
    {
        $JS = <<<JS
            $("#{$this->htmlOptions['id']} button").on('click', function() {
                var items =  $("{$this->gridSelector}").yiiGridView('getSelectedRows');
                if (items.length) {
                    var \$modal = $("{$this->modalSelector}"),
                        action = $(this).data('action'),
                        \$textContainer = $('#mass-publish-modal-text', \$modal);
                        \$textContainer.closest('.alert').removeClass('alert-danger').addClass('alert-info');
                    switch (action) {
                        case '%s' :
                            \$textContainer.text('{$this->publishText}');
                            break;
                        case '%s' :
                            \$textContainer.text('{$this->unpublishText}');
                            break;
                    }
                    \$modal.data('url', "{$this->url}")
                    .data('items', items)
                    .data('switch-action', action)
                    .modal('show');
                }
                return false;
            });
JS;
        $this->view->registerJs(sprintf($JS, self::MASS_PUBLISH, self::MASS_UNPUBLISH));
    }
}
 