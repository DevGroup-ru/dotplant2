<?php
/** @var null|\yii\base\Model $configurationModel */
/** @var \app\extensions\DefaultTheme\models\ThemeWidgets $widget */
/** @var boolean $isAjax */
/** @var \yii\web\View $this */
/** @var \app\extensions\DefaultTheme\models\ThemeActiveWidgets $model */
use kartik\widgets\ActiveForm;
?>
<?= \app\widgets\Alert::widget() ?>
<?php $form = ActiveForm::begin(['id' => 'configure-widget-json-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>
<?= $form->field($configurationModel, 'header') ?>
<?= $form->field($configurationModel, 'displayHeader')->checkbox() ?>
<?php if (!empty($widget->configuration_view)): ?>
    <?=
        $this->render(
            $widget->configuration_view,
            [
                'form' => $form,
                'configurationModel' => $configurationModel,
                'widget' => $widget,
                'isAjax' => $isAjax,
                'model' => $model,
            ]
        )
    ?>
<?php else: ?>
    <?= $form->field($configurationModel, 'configuration_json')->widget(\devgroup\jsoneditor\Jsoneditor::className()) ?>
<?php endif;?>
<?= \app\backend\components\Helper::saveButtons($model, 'index', 'configure-json', $isAjax) ?>
<?php ActiveForm::end(); ?>