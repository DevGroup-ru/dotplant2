<?php

use yii\helpers\Json;
use yii\helpers\Url;

$urlEdit = Json::encode(Url::to($routes['edit']));
$urlIndex = Url::toRoute('index');
$this->registerJs(
    "\$(function(){
    jstree = $(\"#$id\").jstree($options);

jstree.on('dblclick.jstree', function (e, data) {
var \$object = \$(e.target).closest(\"a\");
document.location = $urlEdit + '?id=' + \$object.attr('data-id') + '&parent_id=' + \$object.attr('data-parent-id');
});

$('#$id').on('activate_node.jstree', function (e, data) {
jstree.jstree().save_state();
document.location = '$urlIndex?parent_id=' + data.node.id;
});
});"
);
?>
<div id="<?=$id?>"></div>
