<?php

/* @var $this yii\web\View */
/* @var $model app\modules\shop\models\CurrencyRateProvider */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Currency rate providers'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<?php $form = ActiveForm::begin(['type'=>ActiveForm::TYPE_VERTICAL]); ?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

        <?php
            BackendWidget::begin(
                [
                    'icon' => 'gear',
                    'title'=> Yii::t('app', 'Currency rate provider'),
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'class_name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'params')->textarea() ?>


        <?php BackendWidget::end(); ?>

</div>

<?php ActiveForm::end(); ?>


<div class="clearfix"></div>