<?php

/**
 * @var yii\web\View $this
 */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use \kartik\form\ActiveForm;

$this->title = Yii::t('app', 'Creating new rating group');
$this->params['breadcrumbs'][] = ['url' => [\yii\helpers\Url::toRoute('index')], 'label' => Yii::t('app', 'Rating groups')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['index']),
        ['class' => 'btn btn-danger']
    )
    ?>

    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )
    ?>
</div>
<?php $this->endBlock('submit'); ?>

<?php $form = ActiveForm::begin(['id' => 'item-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <section id="widget-grid">
        <div class="row">
            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <?php BackendWidget::begin(['title'=> Yii::t('app', 'Common'), 'icon'=>'pencil', 'footer'=>$this->blocks['submit']]); ?>
                <?= $form->field($model, 'name'); ?>
                <?= $form->field($model, 'rating_group'); ?>
                <?= $form->field($model, 'min_value'); ?>
                <?= $form->field($model, 'max_value'); ?>
                <?= $form->field($model, 'step_value'); ?>
                <?php BackendWidget::end(); ?>
            </article>
        </div>
    </section>
<?php ActiveForm::end(); ?>