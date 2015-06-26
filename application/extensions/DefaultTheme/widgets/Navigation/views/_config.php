<?php
/** @var null|\yii\base\Model $configurationModel */
/** @var \app\extensions\DefaultTheme\models\ThemeWidgets $widget */
/** @var boolean $isAjax */
/** @var \yii\web\View $this */
/** @var \app\extensions\DefaultTheme\models\ThemeActiveWidgets $model */
/** @var \kartik\widgets\ActiveForm $form */

?>
<?= $form->field($configurationModel, 'rootNavigationId') ?>
<?= $form->field($configurationModel, 'depth') ?>
<?= $form->field($configurationModel, 'linkTemplate') ?>
<?= $form->field($configurationModel, 'submenuTemplate') ?>
<?= $form->field($configurationModel, 'viewFile') ?>
<?= $form->field($configurationModel, 'options')->widget(\devgroup\jsoneditor\Jsoneditor::className()) ?>
