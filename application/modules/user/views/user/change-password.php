<?php

/**
 * @var $model \app\modules\user\models\User
 * @var $this yii\web\View
 */

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Change a password');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' => '/cabinet'
    ],
    $this->title,
];

?>
<h1><?= $this->title ?></h1>
<?= \app\widgets\Alert::widget() ?>
<div class="row">
    <div class="col-md-4 col-sm-12">
        <?php
            $form = ActiveForm::begin(
                [
                    'action' => \yii\helpers\Url::toRoute(['/user/user/change-password']),
                    'id' => 'change-password-form',
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'method' => 'POST',
                ]
            );
        ?>
            <?= $form->field($model, 'password')->passwordInput()->label(Yii::t('app', 'Current password')) ?>
            <?= $form->field($model, 'newPassword')->passwordInput() ?>
            <?= $form->field($model, 'confirmPassword')->passwordInput() ?>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>