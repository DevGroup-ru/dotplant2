<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\data\models\ImportModel $model */

?>

<?= $form->field($model, 'fields[object][]')->checkboxList(
    $fields['object'],
    [
        'item' => function ($index, $label, $name, $checked, $value) {
            $line = Html::beginTag('div', ['class' => 'checkbox']);
            $line .= Html::checkbox($name, $checked, [
                'value' => $value,
                'label' => Html::encode($label),
            ]);
            $line .= '</div>';
            return $line;
        }
    ]
) ?>
