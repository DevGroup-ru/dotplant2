<?php

namespace app\widgets\ace;

use yii\base\Widget;

class Ace extends Widget
{
    public $mode = 'php';
    public $name = '';
    public $options = [];
    public $theme = 'github';
    public $value = '';

    public function run()
    {
        $view = $this->getView();
        AceAsset::register($view);
        $view->registerJs(
            "$('textarea[name={$this->name}]').each(function(index, element) {
                var textarea = $(this);

                var editDiv = $('<div>', {
                    width: textarea.width(),
                    height: textarea.height(),
                    class: textarea.attr('class')
                }).insertBefore(textarea);

                textarea.addClass('hidden');

                var editor = ace.edit(editDiv[0]);
                editor.getSession().setValue(textarea.val());
                editor.getSession().setMode('ace/mode/{$this->mode}');
                editor.setTheme('ace/theme/{$this->theme}');

                editor.getSession().on('change', function() {
                    textarea.val(editor.getSession().getValue());
                });
            });"
        );
        return $this->render('ace', ['name' => $this->name, 'value' => $this->value, 'options' => $this->options]);
    }
}
