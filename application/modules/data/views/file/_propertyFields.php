<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\modules\data\models\ImportModel $model */
/* @var $availablePropertyGroups */

?>
<fieldset>
    <legend><?= Yii::t('app', 'Properties') ?></legend>
    <?php foreach ($availablePropertyGroups as $id => $properties): ?>
        <?php
            $propertyGroup = \app\models\PropertyGroup::findById($id);
        ?>
        <fieldset class="property-group">
            <legend><?= Html::encode($propertyGroup->name) ?></legend>

            <?php
                foreach ($properties as $key => $propertyValue) {
                    echo
                        '<div class="well well-sm well-light"><b>' .
                        \app\models\Property::findById($propertyValue->property_id)->name .' (<small>'.$key.'</small>)' .
                        '</b>';

                    $prefix =
                        'fields[property][' .
                        $propertyValue->property_id .
                        '][';

                    echo $form->field(
                        $model,
                        $prefix . 'enabled]'
                    )->checkbox(['label'=>Yii::t('app', 'Process')]);

                    $property = \app\models\Property::findById($propertyValue->property_id);
                    if ($property->has_static_values) {
                        echo
                            $form->field(
                                $model,
                                $prefix . 'processValuesAs]'
                            )
                            ->dropDownList([
                                'text' => Yii::t('app', 'Text representation'),
                                'id' => 'id ' . Yii::t('app', '(static value record id)'),
                                'value' => Yii::t('app', 'Value representation'),
                            ])
                            ->label(Yii::t('app', 'Process values as:'));
                    }
                    echo Html::activeHiddenInput($model, $prefix. 'key]', ['value' => $key]);
                    echo '</div><br>';
                }
            ?>
        </fieldset>
    <?php endforeach; ?>
</fieldset>
