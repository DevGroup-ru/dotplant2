<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\modules\data\models\ImportModel $model */

$objectClass = \app\models\Object::findById($model->object)->object_class;

$object = new $objectClass;

foreach ($fields['object'] as $name => $value) {

    $fields['object'][$name] = $object->getAttributeLabel($value);
}





?>

<?=
    $form->field($model, 'fields[object][]')->checkboxList(
        $fields['object'],
        [
            'item' => function ($index, $label, $name, $checked, $value) {
                $line = Html::beginTag('div', ['class' => 'checkbox']);
                $line .= Html::checkbox($name, $checked, [
                    'value' => $value,
                    'label' => Html::encode($label).' (<small>'.$value.'</small>)',
                ]);
                $line .= '</div>';
                return $line;
            }
        ]
    )
?>
