<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurableModel $model */

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?= $form->field($model, 'minPagesPerList') ?>

        <?= $form->field($model, 'maxPagesPerList') ?>

        <?= $form->field($model, 'pagesPerList') ?>

        <?= $form->field($model, 'searchResultsLimit') ?>
    </div>
</div>