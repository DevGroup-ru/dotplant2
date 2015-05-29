<?php
/**
 * Use existent form
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Contragent $model
 * @var boolean $immutable
 * @var string $action
 * @var \yii\bootstrap\ActiveForm $form
 */
?>

    <h3>Данные о контрагенте</h3>
    <?= $form->field($model, 'type')
        ->dropDownList(
            ['Individual' => 'Individual', 'Self-employed' => 'Self-employed', 'Legal entity' => 'Legal entity'],
            ['readonly' => $immutable]
        ); ?>
    <?php
        /** @var \app\properties\AbstractModel $abstractModel */
        $abstractModel = $model->getAbstractModel();
        $abstractModel->setArrayMode(false);
        foreach ($abstractModel->attributes() as $attr) {
            echo $form->field($abstractModel, $attr)->textInput(['readonly' => $immutable]);
        }
    ?>
