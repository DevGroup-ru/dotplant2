<?php

use kartik\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\helpers\Json;

?>
<div id="<?= $id ?>"></div>
<script>
$(function(){
    jstree = $("#<?= $id ?>").jstree(<?= $options ?>);

    jstree.on('dblclick.jstree', function (e, data) {
        var $object = $(e.target).closest("a");
        document.location = <?= Json::encode(Url::to($routes['edit']))?> + '?id=' + $object.attr('data-id') + '&parent_id=' + $object.attr('data-parent-id');
    });

    $('#<?= $id ?>').on('activate_node.jstree', function (e, data) {
        jstree.jstree().save_state();
        document.location = '<?= Url::toRoute('index') ?>?parent_id=' + data.node.id;
    });
});
</script>