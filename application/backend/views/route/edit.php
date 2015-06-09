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
<script type="x-tmpl-underscore" id="part-class-template">
    <select class="part_class" id="new_part_class">
        <?php foreach ($settings_by_class as $class_name => $settings): ?>
        <option value="<?= $class_name ?>"><?= $class_name ?></option>
        <?php endforeach;?>
    </select>
</script>
<script type="x-tmpl-underscore" id="part-template">
    <fieldset data-part-id="<%- part_id %>" id="part-<%- part_id %>">
        <legend>
            <span class="part_id"><%- part_id %></span>
            <select class="part_class">
                <?php foreach ($settings_by_class as $class_name => $settings): ?>
                <option value="<?= $class_name ?>"><?= $class_name ?></option>
                <?php endforeach;?>
            </select>
            <a href="#" class="btn btn-xs btn-primary btn-add-part" data-after="#part-<%- part_id %>" data-partid="<%- part_id %>">
                <?= Icon::show('plus') ?>
                Добавить часть после этой
            </a>
        </legend>
        <div class="fields">
            <a href="#" class="btn btn-xs btn-info btn-add-param" data-part-id="<%- part_id %>">
                <?= Icon::show('plus') ?>
                <?= Yii::t('app', 'Add param {0}') ?>
            </a>
        </div>
    </fieldset>
</script>
<script>
$(function(){
    var current_settings = <?= $model->url_template ?>;
    var settings_by_class = <?= Json::encode($settings_by_class) ?>;
//    console.log(settings_by_class, current_settings);

    var part_template = $("#part-template").html(),
        field_template = $("#field-template").html(),
        $url_template = $("#url_template .inner-content"),
        part_class_template = $("#part-class-template").html();

    for (var k in current_settings) {
        var part = current_settings[k];
        $group = group_for_part(part, k);
        $url_template.append($group);
    }

    function addField($group, val, data) {
        var is_null = "";


        if (typeof(val) == "undefined") {
            val = "";
            is_null = 'checked="checked"';
        }

        data['value'] = val;
        data['is_null'] = is_null;
        $field = $(
            _.template(
                field_template,
                data
            )
        );
        $group.find('.fields').append($field);
        return $field;
    }

    function group_for_part(part, part_id) {
//        console.log('group_for_part', part, part_id);
        var $group = $(_.template(
            part_template,
            {
                "part_id": part_id
            }
        ));
        $group.find('.part_class').val(part.class);
        var val = undefined;
        for (var key in settings_by_class[part.class]) {
            if (key == 'parameters') {
                var params = settings_by_class[part.class]['parameters'];
                for (var i in params) {

                    val = typeof(part['parameters'] !== 'undefined') ? part['parameters'][i] : undefined;

                    addField($group, val, {
                        "part_id": part_id,
                        "key": i,
                        "type": settings_by_class[part.class]['parameters'][i],
                        "is_parameters": 1
                    });

                }
            } else {
                val = part[key];
                addField($group, val, {
                    "part_id": part_id,
                    "key": key,
                    "type": settings_by_class[part.class][key],
                    "is_parameters": 0
                });
            }

        }
        return $group;
    }


    $("#url_template").on('click', ".btn-add-part", function(){
        var $dialog_content = $(_.template(
            part_class_template,
            {}
        )),
            $after = $($(this).data('after')),
            partid = parseInt($(this).data('partid'))+1;
        bootbox.dialog({
            title: "New part",
            message: $dialog_content,
            buttons: {
                success: {
                    label: "Add",
                    className: 'btn-success',
                    callback: function(){
                        var class_name = $("#new_part_class").val();

                        var part = {
                            'class': class_name,
                            'part_id': partid
                        };
                        if (settings_by_class[class_name]) {
                            var class_settings=settings_by_class[class_name];
                            for (var key in class_settings) {
                                if (key === 'parameters') {
                                    part[key] = {};
                                    for (var k in class_settings['parameters']) {
                                        part[key][k] = null;
                                    }
                                } else {
                                    part[key] = null;
                                }
                            }
                        }


                        // regroup partids after our
                        $after.parent().find('fieldset').each(function(){
                            var $this = $(this);
                            if (parseInt($this.attr('data-part-id')) >= partid) {
                                $this.attr('data-part-id', parseInt($this.attr('data-part-id'))+1);
                                $this.find('.part_id').text($this.attr('data-part-id'));
                            }
                        });

                        //add our
                        var $group = group_for_part(part, partid);
                        $after.after($group);

                    }
                }
            }
        });

        return false;
    });
})
</script>