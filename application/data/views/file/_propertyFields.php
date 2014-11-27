<?php
use \yii\helpers\Html;

/* @var $form \kartik\widgets\ActiveForm */
/* @var $fields array */
/* @var \app\data\models\ImportModel $model */
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
                        $key .
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
                                'id' => 'id',
                                'text' => Yii::t('app', 'Text representation'),
                            ])
                            ->label(Yii::t('app', 'Process values as:'));
                    }
                    echo Html::activeHiddenInput($model, $prefix.'key]',['value'=>$key]);
                    echo '</div><br>';
                }
            ?>
        </fieldset>
    <?php endforeach; ?>
</fieldset>
