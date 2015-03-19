<?php

use kartik\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;

?>
<div class="jarviswidget" id="">

    <header>
        <h2><?=Icon::show('sitemap')?> <?=Yii::t('app', 'Generate Product Options')?></h2>
    </header>

    <div>
        <!-- widget edit box -->
        <div class="jarviswidget-editbox">
            <!-- This area used as dropdown edit box -->
            <input class="form-control" type="text">
        </div>
        <!-- end widget edit box -->
        <!-- widget content -->
        <div class="widget-body">
            <?=$form->field($groupModel, 'id')->dropDownList(
                    $groups
                );
            ?>
            <?php
            foreach ($properties as $prop) :
                $property_values = app\models\PropertyStaticValues::getValuesForPropertyId($prop->id); ?>
                <div class="form-group">
                    <label class="col-md-2 control-label"><?=$prop->name?></label>

                    <div class="col-md-10">
                        <?php foreach ($property_values as $property_value) : ?>
                            <?php
                            $checked = false;
                            if (isset($optionGenerate['values'][$prop->id][$property_value['id']])) {
                                $checked = true;
                            }
                            ?>
                            <?=Html::checkbox(
                                'GeneratePropertyValue[' . $prop->id . '][' . $property_value['id'] . ']',
                                $checked,
                                ['label' => $property_value['name']]
                            )?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php
            endforeach;
            ?>
            <div class="clearfix"></div>
            <?=$footer?>
        </div>
        <!-- end widget content -->
    </div>
    <!-- end widget div -->
    <script>
        $('#propertygroup-id').change(function () {
            var input = $("<input>").attr("type", "hidden").attr("name", "action").val("save");
            $(this).parents('form').append($(input));
            $(this).parents('form').submit();
        });
        $('#btn-generate').click(function () {
            $.ajax({
                'url': '<?= Url::toRoute(['/backend/product/generate', 'id'=>$model->id]) ?>',
                'method': 'POST',
                'data': $('form').serialize()
            }).done(function () {
                location.reload();
            });
            return false;
        });
    </script>
</div><!-- end widget -->