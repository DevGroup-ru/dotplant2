<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\modules\data\models\ImportModel $model */

?>
<fieldset>
    <legend><?=Yii::t('app', 'Additional fields')?></legend>
    <?php

        foreach ($fields['additionalFields'] as $field_key => $options) {
            echo
                '<div class="well well-sm well-light"><b>' .
                $options['label']. ' (<small>'.$field_key.'</small>)'  .
                '</b>';

            $prefix =
                'fields[additionalFields][' .
                $field_key .
                '][';

            echo $form->field(
                $model,
                $prefix . 'enabled]'
            )->checkbox(['label'=>Yii::t('app', 'Process')]);


            if (isset($options['processValueAs'])) {
                echo
                $form->field(
                    $model,
                    $prefix . 'processValuesAs]'
                )
                    ->dropDownList($options['processValueAs'])
                    ->label(Yii::t('app', 'Process values as:'));
            }
            echo Html::activeHiddenInput($model, $prefix. 'key]', ['value' => $field_key]);
            echo '</div><br>';
        }
    ?>

</fieldset>