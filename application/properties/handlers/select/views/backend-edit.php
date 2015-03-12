<?php

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

<?php
    // little style fix
    if (!$multiple) {
        echo '<style>.field-' . \kartik\helpers\Html::getInputId($model, $property_key) . ' .select2-container .select2-choice .select2-arrow b {background: none;}</style>';
    }
?>

    <div class="form-group field-<?= \kartik\helpers\Html::getInputId($model, $property_key) ?>">
        <?= \yii\helpers\Html::activeLabel($model, $property_key, ['class' => 'col-md-2 control-label']); ?>
        <div class="col-md-10">
            <?=
                kartik\widgets\Select2::widget(
                    [
                        'name' => \yii\helpers\Html::getInputName($model, $property_key),
                        'data' => ['' => ''] + app\models\PropertyStaticValues::getSelectForPropertyId($property_id),
                        'options' => [
                            'multiple' => $multiple ? true : false,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'value' => is_array($model->$property_key) ? $model->$property_key : explode(', ', $model->$property_key),
                    ]
                )
            ?>
        </div>
    </div>
