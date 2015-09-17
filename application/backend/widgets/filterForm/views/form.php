<?php
/**
 * @var $data array
 * @var $this \yii\web\View
 * @var $widgetId string
 * @var $fieldName string
 **
 */

use kartik\select2\Select2;
use kartik\icons\Icon;

?>


<div class="form-group field-importmodel-filterbycategory">
    <label class="col-md-2 control-label" for="'+condition_name +'"><?= $fieldLabel ?></label>

    <div class="col-md-10">
        <?= Select2::widget([
            'id' => 'select-' . $widgetId,
            'name' => $fieldName,
            'value' => '',
            'data' => $data,
            'options' => ['placeholder' => Yii::t('app', 'Select ...')]
        ]); ?>
    </div>

    <div class="col-md-offset-2 col-md-10">
        <div class="help-block form-group">

            <a id="button-<?= $widgetId ?>"
               class="btn btn-md btn-primary pull-right"><?= Icon::show('plus') ?><?= Yii::t('app',
                    'Add condition') ?></a>

        </div>
    </div>

    <div id="<?= 'conditions-' . $widgetId ?>" class="col-md-12">
    </div>
</div>

<?php $this->beginBlock('widgetJs'); ?>
        $("#button-<?=$widgetId?>").click(function () {
            condition_value = $("#select-<?=$widgetId?> option:selected").val();
            condition_label = $("#select-<?=$widgetId?> option:selected").text();
            condition_name = 'ImportModel[conditions][<?= $fieldName ?>][' + condition_value + ']';
            andConditions = <?=json_encode($andConditions);?>;
            operators = <?=json_encode($operators);?>;

            if (condition_value) {

                text = '<div id="<?= $fieldName ?>_form' + condition_value + '" class="form-group">';
                text = text + '<div class=" col-md-1"> ';

                if (andConditions.length != 0) {
                    text = text + '<select name="' + condition_name + '[comparison]" class="form-control">';
                    andConditions.forEach(function (value) {
                        text = text + '<option value="' + value + '">' + value + '</option>';
                    });
                    text = text + '</select>';
                }
                text = text + '</div>';
                text = text + '<div class=" col-md-1">';


                text = text + condition_label + '<input value="' + condition_value + '" name="' + condition_name + '[value]" type="hidden"></div>';
                text = text + '<div class=" col-md-1"> ';

                if (operators.length != 0) {
                    text = text + '<select name="' + condition_name + '[operators]" class="form-control">';
                    operators.forEach(function (value) {
                        text = text + '<option value="' + value + '">' + value + '</option>';
                    });
                    text = text + '</select>';
                }

                text = text + '</div>';

                text = text + '<div class="col-md-8">';
                if (operators.length != 0) {
                    text = text + '<input class="form-control" value="" name="' + condition_name + '[option]" type="text">';
                }
                text = text + '</div>';
                text = text + '<div class="col-md-1"><a class=" btn btn-danger btn-remove" onclick="$(\'#<?= $fieldName ?>_form' + condition_value + '\').remove()"><?= Yii::t('app','Delete') ?></a></div>';
                text = text + '</div>';
            }
            $("#<?='conditions-'.$widgetId?>").append(text)

        });
<?php $this->endBlock(); ?>
<?php $this->registerJs(
    $this->blocks['widgetJs'],
    \yii\web\View::POS_READY,
    'widgetJs'.$widgetId
); ?>
