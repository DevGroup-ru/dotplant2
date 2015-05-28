<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\core\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'composerHomeDirectory') ?>
        <?= $form->field($model, 'internalEncoding') ?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12">


    </div>
</div>

