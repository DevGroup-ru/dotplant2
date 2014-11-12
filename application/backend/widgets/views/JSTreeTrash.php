<?php

use yii\helpers\Url;

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