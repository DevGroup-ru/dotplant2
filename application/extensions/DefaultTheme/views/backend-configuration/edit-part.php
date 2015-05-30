<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
/** @var $this \yii\web\View */
$this->title = Yii::t('app', 'Theme part edit');
$this->params['breadcrumbs'][] = ['url' => [Url::toRoute('index')], 'label' => Yii::t('app', 'Default theme configuration')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'view-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>

<?= \app\backend\components\Helper::saveButtons($model) ?>

<?php $this->endBlock(); ?>

    <section id="widget-grid">
        <div class="row">

            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

                <?php BackendWidget::begin(['title'=> Yii::t('app', 'Common'), 'icon'=>'pencil', 'footer'=>$this->blocks['submit']]); ?>

                <?= $form->field($model, 'name'); ?>
                <?= $form->field($model, 'key'); ?>
                <?= $form->field($model, 'global_visibility')->checkbox(); ?>
                <?= $form->field($model, 'multiple_widgets')->checkbox(); ?>

                <?php BackendWidget::end(); ?>

            </article>

            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <?php BackendWidget::begin(['title'=> Yii::t('app', 'Cache settings'), 'icon'=>'database', 'footer'=>$this->blocks['submit']]); ?>
                <?= $form->field($model, 'is_cacheable')->checkbox(); ?>
                <?= $form->field($model, 'cache_lifetime'); ?>
                <?= $form->field($model, 'cache_tags')->textarea(); ?>
                <?= $form->field($model, 'cache_vary_by_session')->checkbox(); ?>
                <?php BackendWidget::end() ?>
            </article>

        </div>
    </section>

<?php ActiveForm::end(); ?>
