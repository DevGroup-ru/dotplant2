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
    var jstree = $("#<?= $id ?>").jstree(<?= $options ?>);

    $('#<?= $id ?>').on('activate_node.jstree', function (e, data) {
        jstree.jstree().save_state();
        document.location = '<?= Url::toRoute('index') ?>?parent_id=' + data.node.id;
    });
});
</script>