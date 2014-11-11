<?php

/**
 * @var yii\web\View $this
 * @var \app\backend\models\AuthItemForm $model
 * @var array $items
 * @var array $children
 */

use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\widgets\BackendWidget;
use kartik\widgets\ActiveForm;

$isNewRecord = isset($isNewRecord) && $isNewRecord;
$this->title = Yii::t('app', ($isNewRecord ? 'Create ' : 'Update ') . Yii::$app->params['rbacType'][$model->type]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Rbac'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'lock',
                    'title'=> $this->title,
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
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