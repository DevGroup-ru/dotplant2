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
    'rootCategoryId'
) ?>

<?= $form->field(
    $configurationModel,
    'type'
)->dropDownList(
    [
        'plain' => Yii::t('app', 'Plain'),
        'tree' => Yii::t('app', 'Tree')
    ]
) ?>
<?= $form->field(
    $configurationModel,
    'activeClass'
) ?>
<?= $form->field(
    $configurationModel,
    'activateParents'
)->checkbox() ?>
