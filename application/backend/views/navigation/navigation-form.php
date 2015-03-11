<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Navigation edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/navigation/index'], 'label' => Yii::t('app', 'Navigation')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'navigation-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/navigation/index', 'id' => $model->id]),
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

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Navigation item'), 'icon'=>'navicon', 'footer'=>$this->blocks['submit']]); ?>

            <?= $form->field($model, 'name'); ?>
            <?= $form->field($model, 'advanced_css_class'); ?>
            <?= $form->field($model, 'sort_order'); ?>

            <?php BackendWidget::end(); ?>
        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Url'), 'icon'=>'link', 'footer'=>$this->blocks['submit']]); ?>

            <?= $form->field($model, 'url'); ?>
            <?= $form->field($model, 'route'); ?>
            <input type="hidden" name="Navigation[route_params]" id="route_params">
            <div id="properties">
                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <a href="#" class="btn btn-md btn-primary add-property">
                            <?= Icon::show('plus') ?>
                            <?= Yii::t('shop', 'Add property') ?>
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


<script type="x-tmpl-underscore" id="parameter-template">
    <div id="parameter_<%- index %>" class="form-group row parameter">
        <label class="col-md-2 control-label" for="key_<%- index %>">Key</label>
        <div class="col-md-3"><input type="text" id="key_<%- index %>" class="form-control param-key" value=""></div>
        <label class="col-md-1 control-label" for="value_<%- index %>">Value</label>
        <div class="col-md-4"><input type="text" id="value_<%- index %>" class="form-control param-val" value=""></div>
        <div class="col-md-1">
            <a class="btn btn-danger btn-remove">
                <?= Icon::show('thrash-o') ?>
                <?= Yii::t('shop', 'Remove') ?>
            </a>
        </div>
    </div>
</script>

<script>
    $(function(){

        function addProperty(key, value) {
            var index = $('.parameter').length;
            var $property = $(
                _.template(
                    $("#parameter-template").html(),
                    {
                        index : index
                    }
                )
            );

            $property.find('#key_'+index).val(key);
            $property.find('#value_'+index).val(value);

            $property.find('.btn-remove').click(function(){
                $(this).parents('#parameter_'+index).remove();
                return false;
            });

            $("#properties").append($property);
        }


        $(".add-property").click(function(){
            addProperty('', '');
            return false;
        });

        $("#navigation-form").submit(function(){

            var serialized = {};
            $(".parameter").each(function(){
                var key = $(this).find('.param-key').val();
                var value = $(this).find('.param-val').val();
                serialized[key]=value;
            });

            $("#route_params").val(JSON.stringify(serialized));

            return true;
        });

        var current_params = <?= empty($model->route_params) ? "{}" : $model->route_params ?>;
        for (var c in current_params) {
            addProperty(c, current_params[c]);
        }
    });
</script>