<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Json;

$this->title = Yii::t('app', 'Route edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/route/index'], 'label' => Yii::t('app', 'Routes')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'route-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

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

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Route'), 'icon'=>'cogs', 'footer'=>$this->blocks['submit']]); ?>

                <?= $form->field($model, 'name')?>

                <?= $form->field($model, 'route')?>

            <?php BackendWidget::end(); ?>

        </article>

        
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            

            

        </article>

    </div>
</section>



<?php ActiveForm::end(); ?>
<?php BackendWidget::begin(['title'=> Yii::t('app', 'URL Template'), 'icon'=>'cogs', 'footer'=>$this->blocks['submit'], 'id'=>'url_template']); ?>
    <div class="inner-content">
    </div>
<?php BackendWidget::end(); ?>
<script type="x-tmpl-underscore" id="field-template">
    <div class="row form-group field-route-name">
        <label class="col-md-2 control-label" for="Part_<%- part_id %>_<%- key %>">
            <% if (is_parameters) print("<span class=\"badge badge-info\">Param</span>") %>
            <%- key %> <span class="text-muted"><%- type %></span>
        </label>
        <div class="col-md-10">
            <div class="input-group">
                <input type="text" id="Part_<%- part_id %>_<%- key %>" class="form-control" name="<%- key %>" value="<%- value %>" data-is-parameters="<%- is_parameters %>" data-part-index="<%- part_id %>" data-type="<%- type %>">
                <span class="input-group-addon">
                    <input type="checkbox" name="UNSET_<%- key %>" <%- is_null %>>
                </span>
            </div>
        </div>
    </div>
</script>
<script type="x-tmpl-underscore" id="part-template">
    <fieldset data-part-id="<%- part_id %>" id="part-<%- part_id %>">
        <legend>
            <%- part_id %>
            <select class="part_class">
                <?php foreach ($settings_by_class as $class_name => $settings): ?>
                <option value="<?= $class_name ?>"><?= $class_name ?></option>
                <?php endforeach;?>
            </select>
            <a href="#" class="btn btn-xs btn-primary btn-add-part">
                <?= Icon::show('plus') ?>
                Добавить часть после этой
            </a>
        </legend>
        <div class="fields">
            <a href="#" class="btn btn-xs btn-info btn-add-param" data-part-id="<%- part_id %>">
                <?= Icon::show('plus') ?>
                <?= Yii::t('shop', 'Add param {0}') ?>
            </a>
        </div>
    </fieldset>
</script>
<script>
$(function(){
    var current_settings = <?= $model->url_template ?>;
    var settings_by_class = <?= Json::encode($settings_by_class) ?>;
    console.log(settings_by_class, current_settings);

    var part_template = $("#part-template").html(),
        field_template = $("#field-template").html(),
        $url_template = $("#url_template .inner-content");

    for (var k in current_settings) {
        var part = current_settings[k];
        $group = group_for_part(part, k);
        $url_template.append($group);
    }

    function group_for_part(part, part_id) {
        var $group = $(_.template(
            part_template,
            {
                "part_id": part_id
            }
        ));
        $group.find('.part_class').val(part.class);

        for (var key in settings_by_class[part.class]) {
            if (key == 'parameters') {
                var params = settings_by_class[part.class][key];
                for (var i in params) {
                    var val = part['parameters'];
                    if (typeof(val)!="undefined") {
                        val = val[i];
                        if (typeof(val) == "undefined") {
                            val = "";
                            is_null = 'checked="checked"';
                        }
                    } else {
                        val = "";
                        is_null = 'checked="checked"';
                    }
                    $field = $(
                        _.template(
                            field_template,
                            {
                                "part_id": part_id,
                                "key": i,
                                "value": val,
                                "type": settings_by_class[part.class][key][i],
                                "is_parameters": 1,
                                "is_null": is_null
                            }
                        )
                    );
                }
            } else {
                var val = part[key],
                    is_null = "";
                if (typeof(part[key])=="undefined") {
                    val = "";
                    is_null = 'checked="checked"';
                }
                $field = $(
                    _.template(
                        field_template,
                        {
                            "part_id": part_id,
                            "key": key,
                            "value": val,
                            "type": settings_by_class[part.class][key],
                            "is_parameters": 0,
                            "is_null": is_null
                        }
                    )
                );
            }
            $group.find('.fields').append($field);
        }
        return $group;
    }
})
</script>