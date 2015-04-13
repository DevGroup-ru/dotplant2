<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\config\models\BaseConfigurableModel $model */
?>

<?= $form->field($model, 'loginSessionDuration') ?>

<?= $form->field($model, 'passwordResetTokenExpire') ?>
