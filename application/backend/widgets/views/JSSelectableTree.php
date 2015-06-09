<?php

use kartik\helpers\Html;

?>
<div id="<?= $id; ?>">
    <div id="<?= $id ?>-jstree"></div>
    <?= Html::label($selectLabel, $id . '-select', $selectLabelOptions); ?>
    <?= Html::activeDropDownList($model, $flagFieldName, [], $selectOptions); ?>
</div>
<?php ob_start(); ?>
    $("#<?= $id; ?>-jstree").jstree(<?= $options ?>).parents('form').eq(0).submit(function() {
        $('#<?= $id; ?> input[type=hidden]').remove();
        var ids = $("#<?= $id; ?>-jstree").jstree('get_selected');
        for (var key in ids) {
            $('<input />').attr('type', 'hidden').attr('name', '<?= Html::getInputName($model, $fieldName) . ($multiple ? '[]' : ''); ?>').attr('value', ids[key]).appendTo($('#<?= $id; ?>'));
        }
        return true;
    });
    $("#<?= $id; ?>-jstree").bind("dblclick.jstree", function (event) {
        var $object = $(event.target).closest("a");
    }).bind('changed.jstree', function(e, data) {
        var $select = $('#<?= $id; ?>-select');
        switch (data.action) {
            case 'model':
                var selected = $("#<?= $id; ?>-jstree").jstree('get_selected', 'full');
                for (var key in selected) {
                    if ($select.find('option[value=' + selected[key].id + ']').length == 0) {
                        var $option = $('<option />').attr('value', selected[key].id).text(selected[key].text);
                        if (selected[key].id == <?= (int)$model->$flagFieldName; ?>) {
                            $option.attr('selected', 'selected');
                        }
                        $option.appendTo($select);
                    }
                }
                break;
            case 'select_node':
                if ($("#<?=$id?>-select").find('option[value='+data.node.id+']').length === 0) {
                    $('<option />').attr('value', data.node.id).text(data.node.text).appendTo($select);
                }
                <?php if ($selectParents): ?>
                data.instance.select_node(data.node.parent);

                <?php endif; ?>
                break;
            case 'deselect_node':
                $select.find('option[value=' + data.node.id + ']').remove();
                break;
        }
    });
<?php
    $treeScript = ob_get_clean();
    $this->registerJs($treeScript, \yii\web\View::POS_READY);
?>