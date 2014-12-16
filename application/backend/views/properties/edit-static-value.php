<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Property static value edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/index'], 'label' => Yii::t('app', 'Property groups')];
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/group', 'id'=>$model->property->property_group_id], 'label' => $model->property->group->name];
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/edit-property', 'id'=>$model->property_id], 'label' => $model->property->name];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'static-value-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
    ) ?>
</div>
<?php $this->endBlock('submit'); ?>


<section id="widget-grid">
    <div class="row">
        
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Property static value'), 'icon'=>'cogs', 'footer'=>$this->blocks['submit']]); ?>

                <?= $form->field($model, 'name')?>

                <?= $form->field($model, 'value', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('copy'),
                                    ['class'=>'btn btn-primary', 'id'=>'copy-value']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])?>

                <?= $form->field($model, 'slug', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('code'),
                                    ['class'=>'btn btn-primary', 'id'=>'translit-slug']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])?>

                <?= $form->field($model, 'sort_order')?>

                <?= $form->field($model, 'title_append', [
                        'addon' => [
                            'append' => [
                                'content' => Html::button(
                                    Icon::show('copy'),
                                    ['class'=>'btn btn-primary', 'id'=>'copy-title-append']
                                ),
                                'asButton' => true,
                            ]
                        ]
                    ])?>
                <?= $form->field($model, 'dont_filter')->checkbox() ?>


            <?php BackendWidget::end(); ?>

        </article>

        
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            

            

        </article>

    </div>
</section>

<?php ActiveForm::end();  ?>
<script>
    $(function(){
        $("#translit-slug").click(function () {
            Admin.makeSlug(
                [
                    "#propertystaticvalues-name", 
                    "#propertystaticvalues-value", 
                ], 
                "#propertystaticvalues-slug"
            );
            return false;
        });

        $("#copy-title-append").click(function () {
            Admin.copyFrom(
                [
                    "#propertystaticvalues-name", 
                    "#propertystaticvalues-value", 
                    "#product-breadcrumbs_label"
                ], 
                "#propertystaticvalues-title_append"
            );
            return false;
        });

        $("#copy-value").click(function () {
            Admin.copyFrom(
                [
                    "#propertystaticvalues-name"
                ], 
                "#propertystaticvalues-value"
            );
            return false;
        });

    });
</script>
