<?php
use yii\helpers\Html;
use app\backend\widgets\BackendWidget;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'E-commerce counters');
$this->params['breadcrumbs'][] = ['label' => 'SEO', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= Html::beginForm('', 'post', ['class' => 'form-horizontal']); ?>
<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
            <?php BackendWidget::begin(['title' => Yii::t('app', 'Google'), 'icon' => 'cog', 'footer' => '']); ?>
            <div class="form-group required">
                <?= Html::label(Yii::t('app', 'Counter Id'), 'GoogleCounter[id]', ['class' => 'col-md-3 control-label']); ?>
                <div class="col-md-9">
                    <?= Html::input('text', 'GoogleCounter[id]', $gaCounter['id'], ['class' => 'form-control']); ?>
                    <span class="help-block">Наименование счетчика, если на странице более одного или стандартное было изменено</span>
                </div>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Active'), 'GoogleCounter[active]', ['class' => 'col-md-3 control-label']); ?>
                <div class="col-md-9"><?= Html::checkbox('GoogleCounter[active]', $gaCounter['active'], ['class' => '']); ?></div>
            </div>
            <?php BackendWidget::end(); ?>
        </article>

        <article class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
            <?php BackendWidget::begin(['title' => Yii::t('app', 'Yandex'), 'icon' => 'cog', 'footer' => '']); ?>
            <div class="form-group required">
                <?= Html::label(Yii::t('app', 'Counter Id'), 'YandexCounter[id]', ['class' => 'col-md-3 control-label']); ?>
                <div class="col-md-9">
                    <?= Html::input('text', 'YandexCounter[id]', $yaCounter['id'], ['class' => 'form-control']); ?>
                    <span class="help-block">Наименование счетчика в формате yaCounterXXXXXX</span><br />
                </div>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Active'), 'YandexCounter[active]', ['class' => 'col-md-3 control-label']); ?>
                <div class="col-md-9"><?= Html::checkbox('YandexCounter[active]', $yaCounter['active'], ['class' => '']); ?></div>
            </div>
            <?php BackendWidget::end(); ?>
        </article>
    </div>
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']); ?>
        </article>
    </div>
</section>
<?= Html::endForm(); ?>