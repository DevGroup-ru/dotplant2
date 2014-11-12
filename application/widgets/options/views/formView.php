<?php

use kartik\helpers\Html;
use yii\helpers\Json;

foreach ($items as $k => $v) {
    $items[$k]['values'] = ['0' => Yii::t('app', 'Choose')] + $items[$k]['values'];
}

?>
<div id="product-options-widget widget-w0">
    <form id="options-widget">
    <?php foreach($items as $key => $item) : ?>
        <div id="property-<?= $key ?>" class="">
            <label><?= $item['name'] ?></label>
            <?= Html::dropDownList($key, '', $item['values']); ?>
        </div>
    <?php endforeach; ?>
    </form>
</div>

<?php
    $this->registerJs("
    var productOptions = ".Json::encode($optionsJson).";
    var productOptionsDeleted = [];

    $('#options-widget select').change(function() {
        var keyProperty = $(this).attr('name');
        var valProperty = $(this).val();
        var keysDelete = [];
        productOptionsDeleted = productOptions.slice(0);

        $('#options-widget select').each(function(i, el) {
            thisVal = $(el).val();
            thisKey = $(el).attr('name');
            $.each(productOptionsDeleted, function(k, v) {
                if (v.values[thisKey] != thisVal && thisVal != 0) {
                    keysDelete.push(k);
                }
            });
        });
        $('#options-widget select option[value!=0]').attr('disabled', '');
        keysDelete.forEach(function(kDel) {
            if (productOptionsDeleted[kDel]) {
                delete(productOptionsDeleted[kDel]);
            }
        });
        productOptionsDeleted.forEach(function(e) {
            $.each(e.values, function(key, value) {
                $('#options-widget select option[value=' + value + ']').removeAttr('disabled');
            });
        });
    });

    $('#options-widget select').mousedown( function() {
        if ($(this).val() != 0) {
            $(this).val(0);
            $(this).trigger('change');
        }
    });
    ", $this::POS_END);
