<?php

namespace app\backend\widgets;

class Select2 extends \kartik\widgets\Select2
{
    public function registerAssets()
    {
        $view = $this->getView();
        if (!empty($this->language) && $this->language != 'en' && $this->language != 'en_US') {
            Select2Asset::register($view)->js[] = 'js/i18n/' . $this->language . '.js';
        } else {
            Select2Asset::register($view);
        }
        $this->pluginOptions['width'] = 'resolve';
        if ($this->pluginLoading) {
            $id = $this->options['id'];
            $loading = "\$('.kv-plugin-loading.loading-{$id}')";
            $groupCss = "group-{$id}";
            $group = "\$('.kv-hide.{$groupCss}')";
            $el = "\$('#{$id}')";
            $callback = <<< JS
function(){
    var \$container = {$el}.select2('open');
    {$el}.removeClass('kv-hide');
    \$container.removeClass('kv-hide');
    {$loading}.remove();
    if (Object.keys({$group}).length > 0) {
        {$group}.removeClass('kv-hide').removeClass('{$groupCss}');
    }
}
JS;
            $this->registerPlugin('select2', $el, $callback);
        } else {
            $this->registerPlugin('select2');
        }
    }
}
