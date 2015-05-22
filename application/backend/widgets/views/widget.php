<?php

use kartik\helpers\Html;

?>
<?= Html::beginTag('div', $options) ?>
 
    <header>
        <h2><?= $title ?></h2>
        <?= $header_append ?>
    </header>

    <div>
        <!-- widget edit box -->
        <div class="jarviswidget-editbox">
            <!-- This area used as dropdown edit box -->
            <input class="form-control" type="text">
        </div><!-- end widget edit box -->

        <!-- widget content -->
        <div class="widget-body">

            <?= $content ?>

            <?= $footer ?>

        </div><!-- end widget content -->
    </div><!-- end widget div -->
</div><!-- end widget -->