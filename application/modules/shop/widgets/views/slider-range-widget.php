<?php
use yii\helpers\Html;
use yii\jui\SliderInput;
use yii\web\View;

$this->registerJs('function changeRange' . $id . '(){
                $("#slider-id-' . $id . '-container").slider("option", "values", [$("#' . $id . '_minAttribute").val(),$("#' . $id . '_maxAttribute").val()]);
                $("#' . $id . '_changeFlag").attr("value", 1);
        }', View::POS_HEAD);


echo Html::tag(
    'div',

    Html::tag('p', $attributeName, ['style' => 'font-weight: 600;']) .
    Html::hiddenInput($changeFlagAttribute, $changeFlagDefaultValue,
        ['id' => $id.'_changeFlag']) .
    Html::textInput($minAttribute, $minValueNow,
        [
            'id' => $id.'_minAttribute',
            'class' => 'input-small pull-left',
            'style' => 'width:50px; margin: 0 0 7px 0;',
            'onchange' => 'changeRange' . $id . '()'
        ]
    ) .
    Html::textInput($maxAttribute, $maxValueNow,
        [
            'id' => $id.'_maxAttribute',
            'class' => 'input-small pull-right',
            'style' => 'width:50px; margin: 0 0 7px 0;',
            'onchange' => 'changeRange' . $id . '()'

        ]
    ) .
    '<div class="clearfix"></div>' .
    SliderInput::widget([
        'name' => 'amount',
        'id' => 'slider-id-' . $id,
        'clientEvents' => [
            'slide' => 'function(event,ui){
                        $("#range-widget-' . $id . ' input#' . $id . '_minAttribute").val(ui.values[0]);
                        $("#range-widget-' . $id . ' input#' . $id . '_maxAttribute").val(ui.values[1]);
                        $("#' . $id . '_changeFlag").attr("value", 1);
                    }',
        ],
        'clientOptions' => [
            'values' => [(int) $minValueNow, (int) $maxValueNow],
            'range' => true,
            'min' => (int) $minValue,
            'max' => (int) $maxValue,
            'step' => (int) $step,


        ],
    ]),

    [
        'class' => 'range form-inline',
        'style' => 'padding: 10px;',
        'data-min' => (int) $minValue,
        'data-max' => (int) $maxValue,
        'data-step' => (int) $step,
        'id' => 'range-widget-' . $id
    ]
);