<?php
/**
 * Use existent form
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Customer $model
 * @var boolean $immutable
 * @var string $action
 * @var \yii\bootstrap\ActiveForm $form
 * @var array $additional
 */
?>
    <?php if (empty($additional['hideHeader'])): ?>
    <h3><?= Yii::t('app', 'Buyer information') ?></h3>
    <?php endif; ?>
    <?= $form->field($model, 'first_name')->textInput(['readonly' => $immutable]); ?>
    <?= $form->field($model, 'middle_name')->textInput(['readonly' => $immutable]); ?>
    <?= $form->field($model, 'last_name')->textInput(['readonly' => $immutable]); ?>
    <?= $form->field($model, 'email')->textInput(['readonly' => $immutable]); ?>
    <?= $form->field($model, 'phone')->textInput(['readonly' => $immutable]); ?>
    <?php
        /** @var \app\properties\AbstractModel $abstractModel */
        $abstractModel = $model->getAbstractModel();
        $abstractModel->setArrayMode(false);
        foreach ($abstractModel->attributes() as $attr) {
            echo $form->field($abstractModel, $attr)->textInput(['readonly' => $immutable]);
        }
    ?>
