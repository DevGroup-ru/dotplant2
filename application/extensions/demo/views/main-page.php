<?php

/*
 * As you can see this is just a simple yii2 view file with several additional variables available
 *
 * For content rendering use $this->blocks!
 */

/**
 * @var \app\components\WebView $this
 * @var $breadcrumbs array
 * @var $model \app\modules\page\models\Page
 */
$this->params['breadcrumbs'] = $breadcrumbs;
use yii\helpers\Html;


// we need id of property that is related to todays-deals
// in production it is better to fill it statically in your template for better performance
$propertyId = \app\models\Property::getDb()->cache(function($db) {
    return \app\models\Property::find()->where(['key'=>'todays_deals'])->select('id')->scalar($db);
}, 86400, new \yii\caching\TagDependency([
    'tags' => [
        \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\models\Property::className())
    ]
]));

?>

<div class="container rows-padding-top-bottom-20">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center">
                <?= isset($this->blocks['h1'])?$this->blocks['h1']:Yii::t('app', 'Today\'s deals') ?>
            </h1>
            <!-- here we use ProductsWidget to show the same products as in Today's deal prefiltered page -->
            <?php if (intval($propertyId) > 0): ?>
            <?= \app\widgets\ProductsWidget::widget([
                    'category_group_id' => 1,
                    'values_by_property_id' => [
                        $propertyId => [1]
                    ],
                    'limit' => 3,
            ]) ?>
            <?php else: ?>
                Can't find property with key 'todays_deals'.
                Check your migrations.
            <?php endif;?>
            <div class="text-center">
                <a href="/todays-deals" class="btn btn-primary">
                    <?= Yii::t('app', 'See all today\'s deals') ?>
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= isset($this->blocks['content']) ? $this->blocks['content'] : 'Empty content - edit it in backend/page section' ?>
        </div>
    </div>
</div>
