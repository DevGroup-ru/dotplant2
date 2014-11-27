<?php

/* @var $this yii\web\View */
/* @var string $type */
/* @var \app\data\models\ImportModel $model */
/* @var array $fields */
/* @var \app\models\Object $object */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use app\data\models\ImportModel;

$this->title = $object->name . ' ' . Yii::t('app', 'Export');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Data'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php if (isset($fields['object']) && !empty($fields['object'])) : ?>
    <?php
    $form = ActiveForm::begin([
        'id' => 'form-data',
        'type'=>ActiveForm::TYPE_HORIZONTAL,
        'options' => [
            'enctype' => 'multipart/form-data',
        ],
    ]);
    ?>
    <?php
        BackendWidget::begin(
            [
                'icon' => 'sign-out',
                'title'=> $object->name . ' - ' . Yii::t('app', 'Export fields'),
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

        <div class="form-group row fields-to-import">
            <div class="col-md-6">
                <?= $this->render('_objectFields',[
                    'form' => $form,
                    'fields' => $fields,
                    'model' => $model,
                ]) ?>
            </div>
            <?php if (isset($fields['property']) && !empty($fields['property'])): ?>
                <div class="col-md-6">
                    <?= $this->render('_propertyFields',[
                        'form' => $form,
                        'fields' => $fields,
                        'model' => $model,
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group row">
            <div class="col-md-12">
                <?= \yii\helpers\Html::activeDropDownList(
                    $model,
                    'type',
                    ImportModel::knownTypes(),
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