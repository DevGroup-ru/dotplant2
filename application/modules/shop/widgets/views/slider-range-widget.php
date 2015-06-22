<?php
use yii\helpers\Html;
use yii\jui\SliderInput;
use yii\web\View;

$this->registerJs('function changeRange' . $id . '(){
                $("#slider-id-' . $id . '-container").slider("option", "values", [$("#' . $minAttribute . '").val(),$("#' . $maxAttribute . '").val()]);
                $("#' . $changeFlagAttribute . '").attr("value", 1);
        }', View::POS_HEAD);


echo Html::tag(
    'div',

    Html::tag('p', $attributeName, ['style' => 'font-weight: 600;']) .
    Html::hiddenInput($changeFlagAttribute, $changeFlagDefaultValue,
        ['id' => $changeFlagAttribute]) .
    Html::textInput($minAttribute, $minValueNow,
        [
            'id' => $minAttribute,
            'class' => 'input-small pull-left',
            'style' => 'width:50px; margin: 0 0 7px 0;',
            'onchange' => 'changeRange' . $id . '()'
        ]
    ) .
    Html::textInput($maxAttribute, $maxValueNow,
        [
            'id' => $maxAttribute,
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
                        $("#range-widget-' . $id . ' input#' . $minAttribute . '").val(ui.values[0]);
                        $("#range-widget-' . $id . ' input#' . $maxAttribute . '").val(ui.values[1]);
                        $("#' . $changeFlagAttribute . '").attr("value", 1);
                    }',
        ],
        'clientOptions' => [
            'values' => [(int)$minValueNow, (int)$maxValueNow],
            'range' => true,
            'min' => (int)$minValue,
            'max' => (int)$maxValue,
            'step' => 10


        ],
    ]),

    [
        'class' => 'range form-inline',
        'style' => 'padding: 10px;',
        'id' => 'range-widget-' . $id
    ]
);