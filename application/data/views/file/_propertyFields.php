<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\data\models\ImportModel $model */

?>

<?= $form->field($model, 'fields[property][]')->checkboxList(
    $fields['property'],
    [
        'item' => function ($index, $label, $name, $checked, $value) {
            $line = \yii\helpers\Html::beginTag('div', ['class' => 'checkbox']);
            $line .= \yii\helpers\Html::checkbox($name, $checked, [
                'value' => $value,
                'label' => \yii\helpers\Html::encode($label),
            ]);
            $line .= '</div>';
            return $line;
        }
    ]
) ?>