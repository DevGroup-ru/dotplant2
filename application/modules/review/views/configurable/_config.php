<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\review\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'maxPerPage') ?>
        <?= $form->field($model, 'pageSize') ?>
        <?php BackendWidget::end() ?>
    </div>
</div>