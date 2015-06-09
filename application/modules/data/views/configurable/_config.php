<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurableModel $model */

use app\backend\widgets\BackendWidget;

?>



<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'exportDirPath') ?>
        <?= $form->field($model, 'importDirPath') ?>
        <?= $form->field($model, 'defaultType')->dropDownList(\app\modules\data\models\ImportModel::knownTypes()) ?>
        <?php BackendWidget::end() ?>
    </div>
</div>