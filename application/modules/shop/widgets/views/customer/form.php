<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Customer $model
 * @var boolean $immutable
 * @var string $action
 */
?>

<?php
    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'customer-form',
        'action' => $action,
        'layout' => 'horizontal',
    ]);
?>
    <?= $form->field($model, 'first_name'); ?>
    <?= $form->field($model, 'middle_name'); ?>
    <?= $form->field($model, 'last_name'); ?>
    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'phone'); ?>
    <?php
        /** @var \app\properties\AbstractModel $abstractModel */
        $abstractModel = $model->getAbstractModel();
        $abstractModel->setArrayMode(false);
        foreach ($abstractModel->attributes() as $attr) {
            echo $form->field($abstractModel, $attr);
        }
    ?>

<?php $form->end(); ?>
