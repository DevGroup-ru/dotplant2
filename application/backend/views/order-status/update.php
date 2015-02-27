<?php

/* @var $this yii\web\View */
/* @var $model app\models\OrderStatus */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Order Statuses'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>

<?php $this->beginBlock('submit'); ?>
<?=
Html::a(
    Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
    Yii::$app->request->get('returnUrl', ['/backend/order-status/index']),
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
)
?>

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
<?php $this->endBlock('submit'); ?>

<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'info-circle',
                    'title'=> Yii::t('shop', 'Order Status'),
                    'footer' => $this->blocks['submit'],
                ]
            );
        ?>
            <?= $form->field($model, 'title')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'short_title')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'label')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'external_id')->textInput(['maxlength' => 38]) ?>
            <?= $form->field($model, 'internal_comment')->textarea(['rows' => 6]) ?>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
