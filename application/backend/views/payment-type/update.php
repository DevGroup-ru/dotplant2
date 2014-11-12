<?php

/**
 * @var $this yii\web\View
 * @var $model app\models\PaymentType
 */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Payment Types'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'usd',
                    'title'=> Yii::t('shop', 'Payment Type'),
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'class')->textInput(['maxlength' => 255]) ?>
            <?=
                $form->field($model, 'params')
                    ->widget(
                        \app\backend\widgets\jsoneditor\Jsoneditor::className(),
                        [
                            'editorOptions' => [
                                'modes' => ['code', 'tree'],
                                'mode' => 'tree',
                                'editable' => new \yii\web\JsExpression('function(node) {
                                        return {
                                            field : false,
                                            value : true
                                        };
                                    }
                                '),
                            ],
                        ]
                    )
            ?>
            <?= $form->field($model, 'logo')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'commission')->textInput() ?>
            <?= $form->field($model, 'active')->widget(\kartik\widgets\SwitchInput::className()) ?>
            <?= $form->field($model, 'payment_available')->widget(\kartik\widgets\SwitchInput::className()) ?>
            <?= $form->field($model, 'sort')->textInput() ?>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
