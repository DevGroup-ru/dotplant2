<?php

use kartik\icons\Icon;

$toggleHolmes = <<<EOD
$('body').toggleClass('holmes-debug');
$(this).toggleClass('active').toggleClass('btn-danger').toggleClass('btn-default');
EOD;

?>
<div class="yii-debug-toolbar-block">
    Holmes CSS
    <button title="Holmes CSS debug" class="btn btn-default holmes-debug-button" onclick="<?= $toggleHolmes; ?>"><?= Icon::show('eye', ['class' => 'fa-lg'], Icon::FA) ?></button>
</div>