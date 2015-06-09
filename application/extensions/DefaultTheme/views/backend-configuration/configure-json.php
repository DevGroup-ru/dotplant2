<?php
/** @var boolean $isAjax */
/** @var \yii\web\View $this */
/** @var \app\extensions\DefaultTheme\models\ThemeActiveWidgets $model */
use kartik\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['id' => 'configure-widget-json-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?= $form->field($model, 'configuration_json')->widget(\devgroup\jsoneditor\Jsoneditor::className()) ?>
<?= \app\backend\components\Helper::saveButtons($model, 'index', 'configure-json', $isAjax) ?>
<?php ActiveForm::end(); ?>
