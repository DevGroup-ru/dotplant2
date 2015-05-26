<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\review\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'email') ?>
        <?=
            $form
                ->field($model, 'notification[Product]')
                ->label(Yii::t('app', 'Enable notification about new product review'))
                ->widget(\kartik\widgets\SwitchInput::className())
        ?>
        <?= $form->field($model, 'emailTemplate[Product]')->label(Yii::t('app', 'Product email notification template')) ?>
        <?=
            $form
                ->field($model, 'notification[Page]')
                ->label(Yii::t('app', 'Enable notification about new page review'))
                ->widget(\kartik\widgets\SwitchInput::className())
        ?>
        <?= $form->field($model, 'emailTemplate[Page]')->label(Yii::t('app', 'Page email notification template')) ?>
        <?php BackendWidget::end() ?>
    </div>
</div>