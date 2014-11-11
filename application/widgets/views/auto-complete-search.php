<?php
/**
 * @var string $listClass
 * @var array $widgetParams
 */
?>
<?= \yii\jui\AutoComplete::widget($widgetParams) ?>
<script>
$(function(){
    $('#<?= $widgetParams['options']['id'] ?>').autocomplete().data('ui-autocomplete')._renderItem = function(ul, item) {
        <?php if (!empty($listClass)): ?>
            ul.addClass('<?= $listClass ?>');
        <?php endif; ?>
        if (typeof(item.template) == 'undefined'){
            return $("<li>")
                .append($("<a>").attr("href", item.url).text(item.anchor))
                .appendTo(ul);
        } else {
            return $(item.template).appendTo(ul);
        }
    };
});
</script>