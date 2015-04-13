<?php

/**
 * @var $assignments array
 * @var $model \app\modules\user\models\User
 * @var $this \yii\web\View
 */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\components\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Users'), 'url' => ['index']],
    $this->title
];

?>
<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/user/backend-user/index']),
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
<?php $this->endBlock(); ?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'user',
                    'title'=> Yii::t('app', 'User'),
                    'footer' => $this->blocks['submit']
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
