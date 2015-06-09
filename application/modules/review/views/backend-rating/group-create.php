<?php

/**
 * @var yii\web\View $this
 */

use app\backend\widgets\BackendWidget;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

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

<?= Html::beginForm(Yii::$app->request->url, 'post', ['class' => 'form-horizontal']); ?>
<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(['title' => Yii::t('app', 'Common'), 'icon' => 'pencil', 'footer' => $this->blocks['submit']]); ?>
            <div class="form-group required">
                <?= Html::label(Yii::t('app', 'Group name'), 'group-name', ['class' => 'col-md-2 control-label']); ?>
                <div class="col-md-10"><?= Html::input('text', 'group-name', '', ['class' => 'form-control']); ?></div>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Require review'), 'group-require-review', ['class' => 'col-md-2 control-label']); ?>
                <div class="col-md-10"><?= Html::checkbox('group-require-review', false, ['class' => '']); ?></div>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Allow guest user to rate'), 'group-allow-guest', ['class' => 'col-md-2 control-label']); ?>
                <div class="col-md-10"><?= Html::checkbox('group-allow-guest', false, ['class' => '']); ?></div>
            </div>
            <?php BackendWidget::end(); ?>
        </article>
    </div>
</section>
<?= Html::endForm(); ?>
