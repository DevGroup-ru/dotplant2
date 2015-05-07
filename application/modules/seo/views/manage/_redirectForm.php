<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var app\modules\seo\models\Redirect $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/seo/manage/redirect']),
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
    <?=
    Html::submitButton(
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


<div class="redirect-form">

    <?php $form = ActiveForm::begin(['id' => 'redirect-form']); ?>

    <?= $model->isNewRecord ? '' : $form->field($model, 'id')->textInput(['disabled' => 'disabled']); ?>

    <?= $form->field($model, 'type')->dropDownList(\app\modules\seo\models\Redirect::getTypes()) ?>

    <?= $form->field($model, 'from')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'to')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'active')->checkbox([0 => 'false', 1 => 'true']) ?>

    <?= $this->blocks['submit'] ?>

    <?php ActiveForm::end(); ?>

</div>
