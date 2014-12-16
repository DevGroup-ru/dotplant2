<?php

/** @var \yii\web\View $this */
/** @var \app\models\Slider $model */
/** @var \app\slider\BaseSliderEditModel $abstractModel  */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('shop', 'Sliders'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

        <?php
        BackendWidget::begin(
            [
                'icon' => 'tag',
                'title'=> Yii::t('shop', 'Slider'),
                'footer' => Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save'),
                    ['class' => 'btn btn-primary']
                ),
            ]
        );
        ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>

        <?=
            $form
                ->field($model, 'slider_handler_id')
                ->dropDownList(
                    \app\components\Helper::getModelMap(\app\models\SliderHandler::className(), 'id', 'name')
                )
        ?>

        <?= $form->field($model, 'image_width') ?>
        <?= $form->field($model, 'image_height') ?>
        <?= $form->field($model, 'resize_big_images')->checkbox() ?>
        <?= $form->field($model, 'resize_small_images')->checkbox() ?>

        <?= $form->field($model, 'css_class') ?>

        <?= $form->field($model, 'custom_slider_view_file') ?>
        <?= $form->field($model, 'custom_slide_view_file') ?>

        <?php BackendWidget::end(); ?>

    </div>
    <div class="col-xs-12 col-sm-6 col-md6 col-lg-6">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'tag',
                'title'=> Yii::t('shop', 'Additional parameters'),
                'footer' => Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save'),
                    ['class' => 'btn btn-primary']
                ),
            ]
        );
        if ($model->handler() !== null) {
            echo $this->render(
                $model->handler()->slider_edit_view_file,
                [
                    'model' => $model,
                    'form' => $form,
                    'abstractModel' => $abstractModel,
                ]
            );
        } else {
            echo Yii::t('app', 'Save slider to configure additional params of slider implementation.');
        }
        BackendWidget::end();
        ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
