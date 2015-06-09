<?php

/**
 * @var yii\web\View $this
 * @var \app\backend\models\AuthItemForm $model
 * @var array $items
 * @var array $children
 */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$isNewRecord = isset($isNewRecord) && $isNewRecord;
$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update') .
     ' ' .
     Yii::t('app', Yii::$app->params['rbacType'][$model->type]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Rbac'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/rbac/index']),
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

<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'lock',
                    'title'=> $this->title,
                    'footer' =>  $this->blocks['submit'] ,
                ]
            );
        ?>
            <?= $form->field($model, 'oldname', ['template' => '{input}'])->input('hidden'); ?>
            <?= $form->field($model, 'type', ['template' => '{input}'])->input('hidden'); ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'description')->textInput(['maxlength' => 255]) ?>
            <?= (!empty($rules)) ? $form->field($model, 'ruleName')->dropDownList($rules, ['prompt' => 'Choose rule']) : '' ?>
            <?= $form->field($model, 'children')->widget('app\widgets\MultiSelect', [
                'items' => $items,
                'selectedItems' => $children,
                'ajax' => false,
            ]) ?>
            <div id="danger" class="alert-danger alert" style="display: none;">
                <span id="text"></span>
            </div>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>