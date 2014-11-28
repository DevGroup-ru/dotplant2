<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\data\models\ImportModel $model */

?>
<fieldset>
    <legend><?=Yii::t('app', 'Additional fields')?></legend>

    <?=
        $form->field($model, 'fields[additionalFields][]')->checkboxList(
            $fields['additionalFields'],
            [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $line = Html::beginTag('div', ['class' => 'checkbox']);
                    $line .= Html::checkbox($name, $checked, [
                        'value' => $value,
                        'label' => Html::encode($label),
                    ]);
                    $line .= '</div>';
                    return $line;
                },

            ]
        )->label(false)
    ?>
</fieldset>