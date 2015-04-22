<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\modules\user\models\RegistrationForm */

$this->title = Yii::t('app', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
if ($model->username_is_temporary) $model->username = '';
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('app', 'Please fill in required fields to complete registration') ?></p>
    <?php $form = ActiveForm::begin(['id' => 'form-signup', 'action' => ['/user/user/complete-registration']]); ?>
    <?= \app\widgets\Alert::widget() ?>
    <div class="row">
        <div class="col-md-6">

            <?= $form->field($model, 'username') ?>
            <?= $form->field($model, 'email') ?>
            <?= $form->field($model, 'first_name') ?>
            <?= $form->field($model, 'last_name') ?>
        </div>
        <div class="col-md-6">

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Complete registration'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
