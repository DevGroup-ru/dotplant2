<?php

/* @var $this yii\web\View */
/* @var string $type */
/* @var \app\backend\models\ImportModel $model */
/* @var array $fields */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Export');
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(); ?>
    <?php
        BackendWidget::begin(
            [
                'icon' => 'sign-out',
                'title'=> $this->title,
                'footer' => Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Submit'),
                    ['class' => 'btn btn-primary']
                ),
            ]
        );
    ?>
        <?= $form->field($model, 'object')->dropDownList(\app\models\Object::getSelectArray()) ?>
    <?php BackendWidget::end() ?>
<?php ActiveForm::end(); ?>
<?php if (isset($fields['object']) && !empty($fields['object'])) : ?>
    <?= \yii\helpers\Html::beginForm('', 'post', ['class' => 'form-horizontal']) ?>
    <?php
        BackendWidget::begin(
            [
                'icon' => 'list',
                'title'=> Yii::t('app', 'Export fields'),
                'footer' => Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Submit'),
                    ['class' => 'btn btn-primary']
                ),
            ]
        );
    ?>
        <?= \yii\helpers\Html::activeHiddenInput($model, 'object') ?>
        <div class="form-group row">
            <div class="col-md-6">
                <?= \yii\helpers\Html::checkboxList(
                    'ImportModel[fields][object][]',
                    isset($model->fields['object']) ? $model->fields['object'] : [],
                    $fields['object'],
                    [
                        'item' => function ($index, $label, $name, $checked, $value) {
                                $line = \yii\helpers\Html::beginTag('div', ['class' => 'checkbox']);
                                $line .= \yii\helpers\Html::checkbox($name, $checked, [
                                    'value' => $value,
                                    'label' => \yii\helpers\Html::encode($label),
                                ]);
                                $line .= '</div>';
                                return $line;
                            }
                    ]
                ) ?>
            </div>
            <?php if (isset($fields['property']) && !empty($fields['property'])) : ?>
                <div class="col-md-6">
                    <?= \yii\helpers\Html::checkboxList(
                        'ImportModel[fields][property][]',
                        isset($model->fields['property']) ? $model->fields['property'] : [],
                        $fields['property'],
                        [
                            'item' => function ($index, $label, $name, $checked, $value) {
                                    $line = \yii\helpers\Html::beginTag('div', ['class' => 'checkbox']);
                                    $line .= \yii\helpers\Html::checkbox($name, $checked, [
                                        'value' => $value,
                                        'label' => \yii\helpers\Html::encode($label),
                                    ]);
                                    $line .= '</div>';
                                    return $line;
                                }
                        ]
                    ) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
            <?= \yii\helpers\Html::button(
                Yii::t('app', 'Select All'),
                [
                    'id' => 'select_all',
                    'class' => 'btn btn-success btn-sm'
                ]
            ) ?>
            <?= \yii\helpers\Html::button(
                Yii::t('app', 'Unselect All'),
                [
                    'id' => 'unselect_all',
                    'class' => 'btn btn-warning btn-sm'
                ]
            ) ?>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-12">
                <?= \yii\helpers\Html::activeDropDownList(
                    $model,
                    'type',
                    \app\backend\models\ImportModel::knownTypes(),
                    [
                        'class' => 'form-control'
                    ]
                ) ?>
            </div>
        </div>
    <?php BackendWidget::end() ?>
    <?= \yii\helpers\Html::endForm() ?>
<?php endif; ?>

<script>
    $(function() {
        $('#select_all').on('click', function() {
            $('input:checkbox').prop('checked', true);
        });
        $('#unselect_all').on('click', function() {
            $('input:checkbox').prop('checked', false);
        });
    });
</script>