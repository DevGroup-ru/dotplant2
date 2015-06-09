<?php

/**
 * @var $model \app\modules\user\models\RegistrationForm
 * @var $this \yii\web\View
 */

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Signup');
$this->params['breadcrumbs'][] = $this->title;

?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="row">
    <div class="col-md-4">
        <?php
            $form = ActiveForm::begin([
                'id' => 'signun-form',
                'type' => ActiveForm::TYPE_VERTICAL,
            ]);
        ?>
        <?= $form->field($model, 'username')->textInput(['autocomplete' => 'off']) ?>
        <?= $form->field($model, 'email')->textInput(['autocomplete' => 'off']) ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'confirmPassword')->passwordInput() ?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Signup'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>