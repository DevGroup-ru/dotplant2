<?php

use yii\helpers\Html;
use yii\helpers\Json;
use kartik\icons\Icon;
/**
 * @var $attribute_name string
 * @var $form \yii\widgets\ActiveForm
 * @var $label string
 * @var $model \app\properties\AbstractModel
 * @var $multiple boolean
 * @var $property_id integer
 * @var $property_key string
 * @var $this \app\properties\handlers\Handler
 * @var $values array
 */

?>
<?php if ($multiple) {
    $echoField = function ($index) use ($form, $model, $property_key) {
        echo $form->field($model, "{$property_key}[{$index}]", [
            'addon' => [
                'append' => [
                    'content' =>
                    Html::button(
                        Icon::show('plus'),
                        ['class'=>'btn btn-default add-property-'.$property_key]
                    ) .
                    Html::button(
                        Icon::show('trash-o'),
                        ['class'=>'btn btn-default remove-property-'.$property_key]
                    ),
                    'asButton' => true,
                ]
            ]
        ]);
    };

    if (count($values->values) > 0) {
        foreach ($values->values as $index => $val) {
            $echoField($index);
        }
    } else {
        $echoField(0);
    }
    $formId = $form->id;

    $this->registerJs(
        '$(".add-property-'.$property_key.'").click(function(){
            var $form = $("#'.$formId.'");
            var $hidden = $(\'<input type="hidden">\');
            $hidden
                .attr(
                    \'name\',
                    \''. \app\properties\HasProperties::FIELD_ADD_PROPERTY . '\'
                ).val('.Json::encode($property_key).');
            $form.append($hidden);
            $form.find(":submit:first").mouseup().click();
            return false;
        });
        $(".remove-property-'.$property_key.'").click(function(){
            $(this).closest(\'.form-group\').remove();
            return false;
        });

        ', \yii\web\View::POS_READY
    );

} else {
    echo $form->field($model, $property_key.'[0]');
}

