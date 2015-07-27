<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\backend\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Floating panel'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'floatingPanelBottom')->checkbox(); ?>
        <?= $form->field($model, 'wysiwygUploadDir'); ?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Backend edit grids'), 'options' => ['class' => 'visible-header']]); ?>
        <?php foreach ($model->getAllBackendEditGrids() as $moduleId => $backendGrids): ?>
            <?php BackendWidget::begin(['title' => Yii::t('app', $moduleId), 'options' => ['class' => 'visible-header']]); ?>
            <?php foreach ($backendGrids as $grid): ?>
                <?=
                $form->field($model, 'backendEditGrids[' . $moduleId . '][' . $grid['key'] . ']')
                    ->dropDownList(\app\backend\BackendModule::backendGridLabels())
                    ->label($grid['label'])
                ?>
            <?php endforeach; ?>
            <?php BackendWidget::end() ?>
        <?php endforeach; ?>
        <?php BackendWidget::end() ?>
    </div>
</div>

