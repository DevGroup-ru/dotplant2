<?php
/**
 * @var yii\web\View $this
 * @var string $query
 * @var integer $page
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use kartik\icons\Icon;


$this->title = Yii::t('app', 'Explore extensions');
$this->params['breadcrumbs'][] = ['url' => ['/core/backend-extensions/index'], 'label' => Yii::t('app', 'Extensions')];
$this->params['breadcrumbs'][] = $this->title;


?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>


<form action="<?= Url::to(['/core/backend-extensions/explore', 'p'=>1])?>" class="form form-inline packages-search">
    <strong><?= Yii::t('app', 'Search extensions') ?></strong>
    <div class="input-group input-group-md">
        <input class="form-control input-md" type="text" placeholder="<?= Yii::t('app', 'Search ...')?>" id="search-package" value="<?= Html::encode($query)?>" name="q">
        <div class="input-group-btn">
            <button type="submit" class="btn btn-default">
                &nbsp;&nbsp;&nbsp;<i class="fa fa-fw fa-search fa-lg"></i>&nbsp;&nbsp;&nbsp;
            </button>
        </div>
    </div>
</form>


<div class="row packages-grid">
    <?php if (count($packages) === 0 && $page === 1): ?>
        <div class="col-md-12">
            <?= Yii::t('app', 'No extensions matching your search criteria') ?>
        </div>
    <?php endif; ?>
    <?php foreach ($packages as $package): ?>
        <div class="col-md-3">
            <div class="package">
                <?= Html::a($package->getName(), ['/core/backend-extensions/show-package', 'name' => $package->getName()], ['class'=>'show-package package-name']) ?>
                <div class="description">
                    <?= Html::encode($package->getDescription()) ?>
                </div>

                <?=
                    Html::a(Yii::$app->formatter->asTruncated($package->getRepository(), 120), $package->getRepository(), ['class'=>'repository-url', 'target'=>'_blank'])
                ?>

                <?= Html::a(
                    Icon::show('eye') . ' ' . Yii::t('app', 'Show package'),
                    ['/core/backend-extensions/show-package', 'name' => $package->getName()],
                    ['class'=>'show-package btn btn-primary btn-md']
                ) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php

$extensionInformation = Json::encode(Yii::t('app', 'Extension information'));


$js = <<<JS

$(".show-package").click(function(){
    var that = $(this),
        url = that.attr('href');

    that.dialogAction(
        url,
        {
            title: $extensionInformation
        }
    );
    return false;
})

JS;
$this->registerJs($js);