<?php
use yii\helpers\Html;
use \kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\modules\user\models\LoginForm */

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>


    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'username') ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
                <div class="text-reset-pass">
                    <?= Yii::t('app', 'If you forgot your password you can') ?>
                    <?= Html::a(Yii::t('app', 'reset it'), ['/user/user/request-password-reset']) ?>.
                </div>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary btn-login']) ?>
                    <?= Html::a(Yii::t('app', 'Sign up'), ['/user/user/signup'], ['class' => 'btn btn-success btn-signup']) ?>
                </div>
            <?php ActiveForm::end(); ?>

            <?= yii\authclient\widgets\AuthChoice::widget([
                'baseAuthUrl' => ['/user/user/auth']
            ]) ?>
        </div>
    </div>
</div>
