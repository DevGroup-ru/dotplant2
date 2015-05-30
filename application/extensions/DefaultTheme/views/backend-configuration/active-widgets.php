<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var \app\extensions\DefaultTheme\models\ThemeActiveWidgets[] $models */
/** @var array[] $allParts */
/** @var \app\extensions\DefaultTheme\models\ThemeVariation $variation */

$this->title = Yii::t('app', 'Theme part active widgets');
$this->params['breadcrumbs'][] = ['url' => [Url::toRoute('index')], 'label' => Yii::t('app', 'Default theme configuration')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'view-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>

<?= \app\backend\components\Helper::saveButtons($part) ?>

<?php $this->endBlock(); ?>

<section id="widget-grid">
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Active widgets'), 'icon'=>'pencil', 'footer'=>$this->blocks['submit']]); ?>

            <?php foreach ($allVariations as $variationRow): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <?= Html::encode($variationRow['name']) ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php foreach ($models as $activeWidget): ?>
                        <?php if ($activeWidget->variation_id != $variationRow['id']) continue; ?>
                        <?= $activeWidget->widget->name ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php BackendWidget::end(); ?>

        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Add widget'), 'icon'=>'plus']); ?>

            <?php BackendWidget::end() ?>
        </article>

    </div>
</section>

<?php ActiveForm::end(); ?>
