<?php

use app\backend\widgets\BackendWidget;
use app\modules\image\widgets\ImageDropzone;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;

\app\backend\assets\NavigationAsset::register($this);

$this->title = Yii::t('app', 'Navigation edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/navigation/index'], 'label' => Yii::t('app', 'Navigation')];
$this->params['breadcrumbs'][] = $this->title;

$routeParams = empty($model->route_params) ? "{}" : $model->route_params;

$this->registerJs(
    'var current_params = ' . $routeParams . ';',
    \yii\web\View::POS_HEAD
);
?>



<?=app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(['id' => 'navigation-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/navigation/index', 'id' => $model->id]),
        ['class' => 'btn btn-danger']
    )?>

    <?php if ($model->isNewRecord): ?>
        <?=Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )?>
    <?php endif; ?>

    <?=Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    )?>

    <?=Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )?>
</div>
<?php $this->endBlock('submit'); ?>

<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Navigation item'), 'icon' => 'navicon', 'footer' => $this->blocks['submit']]
            ); ?>

            <?= $form->field($model, 'active')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?=$form->field($model, 'name');?>
            <?=$form->field($model, 'advanced_css_class');?>
            <?=$form->field($model, 'sort_order');?>

            <?php BackendWidget::end(); ?>

            <?php
            BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'Images'),
                    'icon' => 'image',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>

            <div id="actions">
                <?=\yii\helpers\Html::tag(
                    'span',
                    Icon::show('plus') . Yii::t('app', 'Add files..'),
                    [
                        'class' => 'btn btn-success fileinput-button'
                    ]
                )?>
                <?php
                if (Yii::$app->getModule('elfinder')) {
                    echo \DotPlant\ElFinder\widgets\ElfinderFileInput::widget(
                        ['url' => Url::toRoute(['addImage', 'objId' => $model->object->id, 'objModelId' => $model->id])]
                    );
                }
                ?>
            </div>

            <?=ImageDropzone::widget(
                [
                    'name' => 'file',
                    'url' => ['upload'],
                    'removeUrl' => ['remove'],
                    'uploadDir' => '/theme/resources/product-images',
                    'sortable' => true,
                    'sortableOptions' => [
                        'items' => '.dz-image-preview',
                    ],
                    'objectId' => $model->object->id,
                    'modelId' => $model->id,
                    'htmlOptions' => [
                        'class' => 'table table-striped files',
                        'id' => 'previews',
                    ],
                    'options' => [
                        'clickable' => ".fileinput-button",
                    ],
                ]
            );?>

            <?php BackendWidget::end(); ?>
        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Url'), 'icon' => 'link', 'footer' => $this->blocks['submit']]
            ); ?>

            <?=$form->field($model, 'url');?>
            <?=$form->field($model, 'route');?>
            <input type="hidden" name="Navigation[route_params]" id="route_params">

            <div id="properties">
                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <a href="#" class="btn btn-md btn-primary add-property">
                            <?=Icon::show('plus')?>
                            <?=Yii::t('app', 'Add property')?>
                        </a>
                        <br>
                        <br>
                    </div>
                </div>
            </div>
            <?php BackendWidget::end(); ?>
        </article>
    </div>
</section>

<?php ActiveForm::end(); ?>


<section style="display:none;" data-type="x-tmpl-underscore" id="parameter-template">
    <div id="parameter_<%- index %>" class="form-group row parameter" style="margin-bottom:15px;">
        <label class="col-md-2 control-label" for="key_<%- index %>">Key</label>

        <div class="col-md-3"><input type="text" id="key_<%- index %>" class="form-control param-key" value=""></div>
        <label class="col-md-1 control-label" for="value_<%- index %>">Value</label>

        <div class="col-md-4"><input type="text" id="value_<%- index %>" class="form-control param-val" value=""></div>
        <div class="col-md-1">
            <a class="btn btn-danger btn-remove">
                <?=Icon::show('thrash-o')?>
                <?=Yii::t('app', 'Remove')?>
            </a>
        </div>
    </div>
</section>



