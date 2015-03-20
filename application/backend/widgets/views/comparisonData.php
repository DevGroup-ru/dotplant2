<?php
use kartik\helpers\Html;


/**
 * @var $fields array
 * @var $types array
 * @var $widgetId string
 *
 */


?>

<table id="<?=$widgetId?>" class="table">
    <?php foreach ($fields as $field): ?>
        <tr id="data-<?=$field['key']?>" class="<?= (isset($field['required']) && $field['required']) ? 'required' : '' ?>">
            <td>
                <?= $field['label'] ?>
                <?= (isset($field['required']) && $field['required']) ? '<span class="red">*</span>' : '' ?>
            </td>
            <td>
                <?= Html::dropDownList(
                    'data[' . $field['key'] . '][type]',
                    null,
                    \yii\helpers\ArrayHelper::merge(['' => Yii::t('app', 'Select ...')], $types),
                    [
                        'class' => 'form-control select-list',
                        'data-key' => $field['key'],
                    ]
                )
                ?>
            </td>
            <td>
            </td>
        </tr>

    <?php endforeach; ?>
</table>
<script>

    var <?=$widgetId?>options = <?= json_encode($options) ?>;
    var <?=$widgetId?>data = <?= json_encode($data); ?>;


    $(function(){
        for (var key in <?=$widgetId?>data) {
        $('select[name="data['+key+'][type]"]')
            .find("[value='" + <?=$widgetId?>data[key]['type'] + "']")
            .attr('selected', 'selected');

            selectDropDown(
                <?=$widgetId?>data[key]['type'],
                key,
                <?=$widgetId?>options
            );

            $('select[name="data['+key+'][key]"]')
                .find("[value='" + <?=$widgetId?>data[key]['key'] + "']")
                .attr('selected', 'selected');
        }


       $('#<?=$widgetId?> .select-list').change(function(){

           optionValue = $('option:selected', this).attr('value');
           key = $(this).attr('data-key');
           selectDropDown(optionValue, key, <?=$widgetId?>options );

       });
    });
    function selectDropDown(optionValue, key, values) {
        if ( optionValue == 'field') {
            $('#data-' + key + ' td').last().html(createDropDown(values.fields, key));
        } if ( optionValue == 'property') {
            $('#data-' + key + ' td').last().html(createDropDown(values.properties, key));
        } if ( optionValue == 'relation') {
            $('#data-' + key + ' td').last().html('');
            $('#data-' + key + ' td').last().append(createDropDownRelation(values.relations, key));
            relationKey =  $('select[name="data[' + key + '][relationName]"] :selected').val();
            $('#data-' + key + ' td').last().append(createInfoRelation(values.relations[relationKey]['values'], relationKey));

        } if (!optionValue) {
            $('#data-' + key + ' td').last().html('');
        }
    }
    function createDropDown(data, key) {
        select = '<select name="data[' + key + '][key]" class="form-control">';
        for (var k in data) {
            select += '<option value="' + k + '">' + data[k] + '</option>';
        }
        select += '</select>';

        return select;
    }


    function createDropDownRelation(data, key) {
        result = '<select name="data[' + key + '][relationName]" class="form-control">';
        for (var k in data) {
            result += '<option value="' + k + '">' + data[k]['relationName'] + '</option>';
        }
        result += '</select>';
        return result;
    }

    function createInfoRelation(data, key) {

        console.log(data)

        result = '<select name="data[' + key + '][key]" class="form-control">';
        for (var k in data) {
            result += '<option value="' + k + '">' + data[k] + '</option>';
        }
        result += '</select>';
        return result;
    }




</script>