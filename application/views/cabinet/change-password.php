<?php

/**
 * @var $model app\models\User
 * @var $this yii\web\View
 */

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Change a password');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' => Yii::$app->request->baseUrl.'/cabinet'
    ],
    $this->title,
];

?>
<h1><?= $this->title ?></h1>
<div class="row">
    <div class="span4 well">
        <?php
            $form = ActiveForm::begin(
                [
                    'action' => \yii\helpers\Url::toRoute(['/cabinet/change-password']),
                    'id' => 'change-password-form',
                    'type' => ActiveForm::TYPE_VERTICAL,
                ]
            );
        ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'newPassword')->passwordInput() ?>
            <?= $form->field($model, 'confirmPassword')->passwordInput() ?>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>