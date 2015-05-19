<?php

/**
 * @var $this yii\web\View
 * @var app\backend\components\ActiveForm $form
 * @var \app\modules\shop\models\DiscountCode $object
 */

?>



<?= $form->field($object, 'code'); ?>
<?= $form->field($object, 'valid_from')->widget(\kartik\datetime\DateTimePicker::className()); ?>
<?= $form->field($object, 'valid_till')->widget(\kartik\datetime\DateTimePicker::className()); ?>
<?= $form->field($object, 'maximum_uses'); ?>
