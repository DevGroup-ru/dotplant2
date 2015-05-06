<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\modules\seo\models\Config $model
 * @var yii\widgets\ActiveForm $form
 */

?>

<div class="<?= $model->key ?>-form">

    <?php $form = ActiveForm::begin(['id' => $model->key.'-form']); ?>

    <?= $form->field($model, 'key', ['template' => '{input}'])->input('hidden'); ?>

    <?= $form->field($model, 'value', ['template' => '{input}'])->textarea(['rows' => '25']) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
