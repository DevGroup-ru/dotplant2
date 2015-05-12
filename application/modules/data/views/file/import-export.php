<?php

/* @var $this yii\web\View */
/* @var string $type */
/* @var \app\modules\data\models\ImportModel $model */
/* @var array $fields */
/* @var \app\models\Object $object */
/* @var array $availablePropertyGroups */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use \app\modules\data\assets\DataAsset;

DataAsset::register($this);

$this->title = $object->name . ' ' .
    ($importMode ? Yii::t('app', 'Import') : Yii::t('app', 'Export'));

$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Data'),
    'url' => ['index']
];
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
                'icon' => 'list',
                'title'=> $object->name . ' - ' . Yii::t('app', 'Fields'),
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
        <div class="col-md-4">
            <?= $this->render('_objectFields', [
                'form' => $form,
                'fields' => $fields,
                'model' => $model,
            ]) ?>
        </div>
    <?php if (isset($fields['property']) && !empty($fields['property'])): ?>
        <div class="col-md-4">
            <?= $this->render('_propertyFields', [
                'form' => $form,
                'fields' => $fields,
                'model' => $model,
                'availablePropertyGroups' => $availablePropertyGroups,
            ]) ?>
        </div>
    <?php endif; ?>

        <div class="col-md-4">
            <?= $this->render('_additionalFields', [
                'form' => $form,
                'fields' => $fields,
                'model' => $model,
            ]) ?>
        </div>

    </div>
    <?php if ($importMode === true): ?>
    <div class="form-group row">
        <div class="col-md-12">
            <?= $form->field($model, 'file')->fileInput() ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-12">
            <fieldset>
                <legend>
                    <?= Yii::t('app', 'Add property groups to each new object: ') ?>
                </legend>
                <?=
                    $form->field(
                        $model,
                        'addPropertyGroups'
                    )
                    ->checkboxList(
                        \yii\helpers\ArrayHelper::map(
                                \app\models\PropertyGroup::getForObjectId($object->id),
                                'id',
                                'name'
                            ),
                            [
                                'item' => function ($index, $label, $name, $checked, $value) {
                                    $line = Html::beginTag('div', ['class' => 'checkbox']);
                                    $line .= Html::checkbox($name, $checked, [
                                        'value' => $value,
                                        'label' => Html::encode($label),
                                    ]);
                                    $line .= '</div>';
                                    return $line;
                                }
                            ]
                    )
                    ->label('')
                ?>
            </fieldset>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$importMode): ?>
        <?= $this->render('_searchForm', [
                    'form' => $form,
                    'model' => $model,
                    'object' => $object,
                    'fields' => $fields,
                    'availablePropertyGroups' => $availablePropertyGroups,
        ]) ?>
    <?php endif; ?>

    <div class="form-group row">
        <div class="col-md-12">
            <fieldset>
                <legend><?= Yii::t('app', 'Settings') ?></legend>

                <div class="form-group">
                    <div class='col-md-offset-2 col-md-10'>
                        <div class="checkbox">
                            <?= Html::checkbox('Task[create_notification]', false, ['label' => 'Создать уведомление']); ?>
                        </div>
                    </div>
                </div>

                <?php if ($importMode === true): ?>
                    <?= $form->field($model, 'createIfNotExists')->checkbox() ?>
                <?php endif; ?>
                <?= $form->field($model, 'multipleValuesDelimiter') ?>
            </fieldset>
        </div>
    </div>

    <?php if (!$importMode): ?>
        <div class="form-group row">
            <div class="col-md-12">
                <?= $form->field($model, 'type')->dropDownList(\app\modules\data\models\ImportModel::knownTypes()) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php BackendWidget::end(); ?>
    <?php ActiveForm::end() ?>
<?php endif; ?>

