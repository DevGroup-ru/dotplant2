<?php

/**
 * @var $breadcrumbs array
 * @var $children \app\models\Page[]
 * @var $model \app\models\Page,
 * @var $pages \app\widgets\PagesList
 */

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;

$this->params['breadcrumbs'] = $breadcrumbs;
if ($model->title) {
    $this->title = $model->title;
} elseif ($model->h1) {
    $this->title = $model->h1;
}

?>

<?php
if ($model->h1) {
    echo Html::tag('h1', Html::encode($model->h1));
}
?>
<div class="pages-list-announce">
    <?= $model->announce ?>
</div>

<ul class="media-list pages-list">
    <?php foreach ($children as $child): ?>
        <li class="media">
            <h5>
                <div class="page-date_added label label-default">
                    <?= Icon::show('calendar') ?>
                    <?= date("d.m.Y H:i:s", strtotime($child->date_added)); ?>
                </div>
                <a href="<?= Url::to(['page/show', 'id'=>$child->id])?>" class="page-title">
                    <?= $child->title ?>
                </a>
            </h5>
            <div class="media-body">
                <?= $child->announce ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
<?php if ($pages->pageCount > 1):?>
<?=
    yii\widgets\LinkPager::widget([
        'pagination' => $pages,
    ]);
?>
<?php endif; ?>

<div class="pages-list-content">
    <?= $model->content ?>
</div>
