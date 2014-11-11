<?php

/**
 * @var $model
 * @var $this \yii\web\View
 */

use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;

?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="row">
    <div class="span4 well">
        <?php
            $form = ActiveForm::begin([
                'id' => 'login-form',
                'type' => ActiveForm::TYPE_VERTICAL,
            ]);
        ?>
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
        <?= AuthChoice::widget([
            'baseAuthUrl' => ['default/auth']
        ]) ?>
    </div>
</div>
