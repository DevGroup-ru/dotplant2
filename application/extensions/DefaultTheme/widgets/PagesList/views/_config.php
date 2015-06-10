<?php
/** @var null|\yii\base\Model $configurationModel */
/** @var \app\extensions\DefaultTheme\models\ThemeWidgets $widget */
/** @var boolean $isAjax */
/** @var \yii\web\View $this */
/** @var \app\extensions\DefaultTheme\models\ThemeActiveWidgets $model */
/** @var \kartik\widgets\ActiveForm $form */

?>
<?= $form->field(
    $configurationModel,
    'parent_id'
) ?>

<?= $form->field(
    $configurationModel,
    'limit'
) ?>

<?= $form->field(
    $configurationModel,
    'display_date'
)->checkbox() ?>

<?= $form->field(
    $configurationModel,
    'date_format'
) ?>

<?= $form->field(
    $configurationModel,
    'more_pages_label'
) ?>

<?= $form->field(
    $configurationModel,
    'order_by'
) ?>

<?= $form->field(
    $configurationModel,
    'order'
)->dropDownList([SORT_ASC=>'ASC', SORT_DESC=>'DESC']) ?>

<?= $form->field(
    $configurationModel,
    'view_file'
) ?>



