<?php

/** @var \yii\web\View $this */
/** @var \app\models\Slider $model */
/** @var \app\slider\BaseSliderEditModel $abstractModel  */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use kartik\editable\Editable;
use kartik\popover\PopoverX;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Sliders'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

$editable_formOptions = [
    'action' => 'update-slide',
];


?>
<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/slider/index']),
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

<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

        <?php
        BackendWidget::begin(
            [
                'icon' => 'tag',
                'title'=> Yii::t('app', 'Slider'),
                'footer' => $this->blocks['submit'],
            ]
        );
        ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>

        <?=
            $form
                ->field($model, 'slider_handler_id')
                ->dropDownList(
                    \app\components\Helper::getModelMap(\app\models\SliderHandler::className(), 'id', 'name')
                )
        ?>

        <?= $form->field($model, 'image_width') ?>
        <?= $form->field($model, 'image_height') ?>
        <?= $form->field($model, 'resize_big_images')->checkbox() ?>
        <?= $form->field($model, 'resize_small_images')->checkbox() ?>

        <?= $form->field($model, 'css_class') ?>

        <?= $form->field($model, 'custom_slider_view_file') ?>
        <?= $form->field($model, 'custom_slide_view_file') ?>

        <?php BackendWidget::end(); ?>

    </div>
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'tag',
                'title'=> Yii::t('app', 'Additional parameters'),
                'footer' => $this->blocks['submit'],
            ]
        );
        if ($model->handler() !== null) {
            echo $this->render(
                $model->handler()->slider_edit_view_file,
                [
                    'model' => $model,
                    'form' => $form,
                    'abstractModel' => $abstractModel,
                ]
            );
        } else {
            echo Yii::t('app', 'Save slider to configure additional params of slider implementation.');
        }
        BackendWidget::end();
        ?>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

        <?=
        \kartik\dynagrid\DynaGrid::widget([
            'options' => [
                'id' => 'slides-grid',
            ],
            'columns' => [
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',
                ],
                [
                    'attribute' =>'sort_order',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => Editable::INPUT_TEXT,
                        'formOptions' => $editable_formOptions,
                        'placement' => PopoverX::ALIGN_BOTTOM,
                    ],
                ],
                [
                    'attribute' => 'image',
                    'class' => '\app\components\ImageColumn',

                ],
                [
                    'attribute' => 'link',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => Editable::INPUT_TEXT,
                        'formOptions' => $editable_formOptions,
                        'placement' => PopoverX::ALIGN_BOTTOM,
                    ],
                ],
                [
                    'attribute' => 'text',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => Editable::INPUT_TEXTAREA,
                        'formOptions' => $editable_formOptions,
                        'placement' => PopoverX::ALIGN_BOTTOM,
                    ],
                ],
                [
                    'attribute' => 'custom_view_file',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => Editable::INPUT_TEXT,
                        'formOptions' => $editable_formOptions,
                        'placement' => PopoverX::ALIGN_BOTTOM,
                    ],
                ],
                [
                    'attribute' => 'css_class',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => Editable::INPUT_TEXT,
                        'formOptions' => $editable_formOptions,
                        'placement' => PopoverX::ALIGN_BOTTOM,
                    ],
                ],
                [
                    'attribute' => 'active',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => Editable::INPUT_CHECKBOX,
                        'formOptions' => $editable_formOptions,
                        'placement' => PopoverX::ALIGN_BOTTOM,
                    ],
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->active === null) {
                            return null;
                        }
                        return $model->active ? Icon::show('check txt-color-green') : Icon::show('times txt-color-red');
                    },
                ],
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' => [
                        [
                            'url' => 'delete-slide',
                            'icon' => 'trash-o',
                            'class' => 'btn-danger',
                            'label' => Yii::t('app', 'Delete'),
                        ],
                    ],
                ],
            ],

            'theme' => 'panel-default',

            'gridOptions'=>[
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover'=>true,

                'panel'=>[
                    'heading'=>'<h3 class="panel-title">'.Yii::t('app', 'Slides').'</h3>',
                    'after' =>
                        Html::a(
                            Icon::show('plus') . ' ' .
                            Yii::t('app', 'Add slide'),
                            ['new-slide', 'slider_id' => $model->id, ],
                            [
                                'class' => 'btn btn-primary'
                            ]
                        )

                ],

            ]
        ]);
        ?>

    </div>
</div>
<?php ActiveForm::end(); ?>
<section style="display: none" type="x-tmpl-underscore" id="image-upload-template">
    <?php ActiveForm::begin([
        'action' => 'upload-slide',
        'options' => [
            'enctype' => 'multipart/form-data',
            'type'=>'post',
        ],

    ]); ?>

        <input type="hidden" name="slide_id" value="<%- model_id %>">
        <input type="hidden" name="attribute" value="<%- attribute %>">
        <input type="hidden" name="slider_id" value="<?= $model->id ?>">
        <div class="form-group">
            <label><?= Yii::t('app', 'New image') ?></label>
            <input type="file" name="file">
        </div>
        <button type="submit" class="btn btn-primary"><?= Icon::show('upload') . ' ' . Yii::t('app', 'Upload') ?></button>
    <?php ActiveForm::end() ?>
</section>
<?php
$script = <<< SCRIPT


        $("#slides-grid").on('click', 'a.btn-change-image', function(){
            var \$this = $(this),
                model_id = \$this.data('modelid'),
                attribute= \$this.data('attribute'),
                compile = _.template($("#image-upload-template").html());
                template = compile(
                    {
                        'model_id': model_id,
                        'attribute': attribute
                    }
                )
                ;

            \$this.popover({
                content: template,
                html: true,
                'trigger': 'click'
            }).popover('show');

            return false;
        })
SCRIPT;

$this->registerJs($script, \yii\web\View::POS_READY);

?>
