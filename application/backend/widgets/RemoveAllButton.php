<?php

namespace app\backend\widgets;

use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\Html;

class RemoveAllButton extends Widget
{
    public $url;
    public $gridSelector;
    public $htmlOptions = [];

    public function init()
    {
        if (!isset($this->url, $this->gridSelector)) {
            throw new InvalidParamException('Attribute \'url\' or \'gridSelector\' is not set');
        }

        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = 'deleteItems';
        }

        Html::addCssClass($this->htmlOptions, 'btn');
    }

    public function run()
    {
        $this->registerScript();
        return $this->renderButton();
    }

    protected function renderButton()
    {
        return Html::button(
            \Yii::t('app', 'Delete selected'),
            $this->htmlOptions
        );
    }

    protected function registerScript()
    {
        $this->view->registerJs("
            $('#{$this->htmlOptions['id']}').on('click', function() {
                var items =  $('{$this->gridSelector}').yiiGridView('getSelectedRows');
                if (items.length && confirm('" . \Yii::t('app', 'Are you sure you want to delete these objects?') . "')) {
                    $.ajax({
                        'url': '{$this->url}',
                        'type': 'post',
                        'data': {
                            'items': items
                        },
                        success: function (data) {
                            location.reload();
                        }
                    });
                }
            });
        ");
    }
}
 