<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\image\models\ConfigConfigurableModel $model */

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?= $form->field($model, 'useWatermark')->widget(\kartik\widgets\SwitchInput::className()) ?>
        <?= $form->field($model, 'defaultThumbnailSize') ?>
        <?= $form->field($model, 'noImageSrc') ?>
        <?= $form->field($model, 'thumbnailsDirectory') ?>
        <?= $form->field($model, 'watermarkDirectory') ?>
    </div>
</div>

