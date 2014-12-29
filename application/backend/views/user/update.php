<?php

/**
 * @var $assignments array
 * @var $model \app\models\User
 * @var $this \yii\web\View
 */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Users'), 'url' => ['index']],
    $this->title
];

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'user',
                    'title'=> Yii::t('shop', 'User'),
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?= $form->field($model, 'username')->textInput(['maxlength' => 255, 'autocomplete' => 'off']) ?>
            <?= $form->field($model, 'password')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'status')->dropDownList($model->getStatuses()) ?>
            <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'first_name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'last_name')->textInput(['maxlength' => 255]) ?>
            <?=
                app\widgets\MultiSelect::widget([
                    'items' => \yii\helpers\ArrayHelper::map(
                        \Yii::$app->getAuthManager()->getRoles(),
                        'name',
                        function ($item) {
                            return $item->name . (strlen($item->description) > 0
                                ? ' [' . $item->description . ']'
                                : '');
                        }
                    ),
                    'selectedItems' => $model->isNewRecord ? [] : $assignments,
                    'ajax' => false,
                    'name' => 'AuthAssignment[]',
                    'label' => Yii::t('app', 'Assignments'),
                ]);
            ?>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
