<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var app\modules\seo\models\Meta $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/seo/manage/meta']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?php if ($model->isNewRecord): ?>
        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )
        ?>
    <?php endif; ?>
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    ); ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )
    ?>



</div>
<?php $this->endBlock('submit'); ?>

<div class="meta-form">

    <?php $form = ActiveForm::begin(['id' => 'meta-form']); ?>

    <?= $model->isNewRecord ? $form->field($model, 'key')->textInput() : $form->field($model, 'key')->textInput(['disabled' => 'disabled']); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'content')->textInput(['maxlength' => 255]) ?>

    <?= $this->blocks['submit'] ?>

    <?php ActiveForm::end(); ?>

</div>
