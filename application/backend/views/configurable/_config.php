<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\backend\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Floating panel'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'floatingPanelBottom')->checkbox(); ?>
        <?= $form->field($model, 'wysiwygUploadDir'); ?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12">


    </div>
</div>

