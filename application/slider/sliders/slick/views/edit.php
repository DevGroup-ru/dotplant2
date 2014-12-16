<?php

/** @var \yii\widgets\ActiveForm $form */
/** @var \app\models\Slider $model */
/** @var \app\slider\AbstractSliderWidget $abstractModel */

?>

<?= $form->field($abstractModel, 'autoplaySpeed') ?>
<?= $form->field($abstractModel, 'autoplay')->checkbox() ?>
<?= $form->field($abstractModel, 'speed') ?>

<?= $form->field($abstractModel, 'dots')->checkbox() ?>
<?= $form->field($abstractModel, 'fade')->checkbox() ?>

<?= $form->field($abstractModel, 'prevArrow') ?>
<?= $form->field($abstractModel, 'nextArrow') ?>