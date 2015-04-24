<?php
/**
 * @var string $listClass
 * @var array $widgetParams
 */

$js = "$(function(){
    $('#" . $widgetParams['options']['id'] . "').autocomplete().data('ui-autocomplete')._renderItem = function(ul, item) {";
if (!empty($listClass)) {
    $js .= "ul.addClass('" . $listClass . "');";
}
$js .= "if (typeof(item.template) == 'undefined') {
                return $(\"<li>\")
                    .append($(\"<a>\").attr(\"href\", item.url).text(item.anchor))
                    .appendTo(ul);
            } else {
                return $(item.template).appendTo(ul);
            }
        };
    });";
$this->registerJs($js);
?>
<?=\yii\jui\AutoComplete::widget($widgetParams)?>


