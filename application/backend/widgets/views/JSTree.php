<?php

use yii\helpers\Json;
use yii\helpers\Url;

?>
    <div id="<?=$id?>"></div>
<?php
$edit = Json::encode(Url::to($routes['edit']));
$index = Url::toRoute('index');
$jstree = <<<js
$(function(){
    jstree = $("#{$id}").jstree({$options});

    jstree.on('dblclick.jstree', function (e, data) {
        var \$object = $(e.target).closest("a");
        document.location = {$edit} + '?id=' + \$object.attr('data-id') + '&parent_id=' + \$object.attr('data-parent-id');
    });

    $('#{$id}').on('activate_node.jstree', function (e, data) {
        jstree.jstree().save_state();
        document.location = '{$index}?parent_id=' + data.node.id;
    });
});
js;

$this->registerJS($jstree);
