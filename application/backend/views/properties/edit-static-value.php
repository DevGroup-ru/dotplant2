<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\components\ActiveForm;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Property static value edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/index'], 'label' => Yii::t('app', 'Property groups')];
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/group', 'id'=>$model->property->property_group_id], 'label' => $model->property->group->name];
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/edit-property', 'id'=>$model->property_id, 'property_group_id' => $model->property->property_group_id], 'label' => $model->property->name];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'static-value-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
<?=
Html::a(
    Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
    Yii::$app->request->get('returnUrl', ['/backend/properties/edit-property', 'id' => $model->property_id, 'property_group_id' => $model->property->property_group_id]),
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

<?=
Html::submitButton(
    Icon::show('save') . Yii::t('app', 'Save & Go back'),
    [
        'class' => 'btn btn-warning',
        'name' => 'action',
        'value' => 'back',
    ]
)
?>

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


<section id="widget-grid">
    <div class="row">
        
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Property static value'), 'icon'=>'cogs', 'footer'=>$this->blocks['submit']]); ?>
            <?= $this->render('edit-static-value-form', ['model'=>$model, 'form'=>$form]) ?>
            <?php BackendWidget::end(); ?>
        </article>

        
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php
            BackendWidget::begin(
                [
                    'title'=> Yii::t('app', 'Images'),
                    'icon'=>'image',
                    'footer'=>$this->blocks['submit']
                ]
            ); ?>

            <div id="actions">
                <?=
                \yii\helpers\Html::tag(
                    'span',
                    Icon::show('plus') . Yii::t('app', 'Add files..'),
                    [
                        'class' => 'btn btn-success fileinput-button'
                    ]
                ) ?>
                <?php
                if (Yii::$app->getModule('elfinder')) {
                    echo \DotPlant\ElFinder\widgets\ElfinderFileInput::widget(
                        ['url' => Url::toRoute(['addImage', 'objId' => $object->id, 'objModelId' => $model->id])]
                    );
                }
                ?>
            </div>

            <?= \app\modules\image\widgets\ImageDropzone::widget([
                'name' => 'file',
                'url' => ['/shop/backend-product/upload'],
                'removeUrl' => ['/shop/backend-product/remove'],
                'uploadDir' => '/theme/resources/product-images',
                'sortable' => true,
                'sortableOptions' => [
                    'items' => '.dz-image-preview',
                ],
                'objectId' => $object->id,
                'modelId' => $model->id,
                'htmlOptions' => [
                    'class' => 'table table-striped files',
                    'id' => 'previews',
                ],
                'options' => [
                    'clickable' => ".fileinput-button",
                ],
            ]); ?>

            <?php BackendWidget::end(); ?>
        </article>

    </div>
</section>

<?php ActiveForm::end();  ?>

