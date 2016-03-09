<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\extensions\DefaultTheme\models\ConfigurationModel $model */

use app\backend\widgets\BackendWidget;
use kartik\widgets\ColorInput;
use yii\helpers\Html;
use kartik\icons\Icon;

?>

<div class="row">
    <div class="col-md-5 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Colors'), 'options' => ['class' => 'visible-header']]); ?>
        <?=$form->field($model, 'primary_color')->widget(ColorInput::className(), ['type' => 'color'])?>
        <?=$form->field($model, 'secondary_color')->widget(ColorInput::className(), ['type' => 'color'])?>
        <?=$form->field($model, 'action_color')->widget(ColorInput::className(), ['type' => 'color'])?>

        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-5 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Site settings'), 'options' => ['class' => 'visible-header']]); ?>

            <?= $form->field($model, 'siteName') ?>
            <?= $form->field($model, 'primaryEmail') ?>
            <fieldset>
                <legend><?= Yii::t('app', 'Logotype') ?></legend>
                <div class="form-group">
                    <label for="current-logo" class="control-label">
                        <?= Yii::t('app', 'Current logotype:') ?>
                    </label>
                    <?php if (empty($model->logotypePath)):?>
                        <?= Yii::t('app', 'No image') ?>
                    <?php else: ?>
                        <img src="<?= $model->logotypePath ?>" alt="Your current logo" class="img-responsive"/>
                        <?= $form->field($model, 'logotypeFile')->widget(\kartik\file\FileInput::className()) ?>
                    <?php endif; ?>
                </div>
            </fieldset>


        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-1 col-sm-12">
        <?= Html::a(
            Icon::show('puzzle-piece') . ' ' . Yii::t('app', 'Widgets and parts'),
            ['/DefaultTheme/backend-configuration/index'],
            [
                'class' => 'btn btn-primary btn-sm',
            ]
        ) ?>
    </div>
</div>