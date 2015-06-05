<?php

/**
 * @var $breadcrumbs array
 * @var $children \app\modules\page\models\Page[]
 * @var $model \app\modules\page\models\Page,
 * @var $pages \app\widgets\PagesList
 */

use kartik\icons\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
$this->params['breadcrumbs'] = $breadcrumbs;
?>
<?php
if ($this->blocks['h1']) {
    echo Html::tag('h1', Html::encode($this->blocks['h1']));
}
?>
<div class="pages-list-announce">
    <?= $this->blocks['announce'] ?>
</div>
<ul class="media-list pages-list">
    <?php foreach ($children as $child): ?>
        <li class="media">
            <h5>
                <div class="page-date_added label label-default">
                    <?= Icon::show('calendar') ?>
                    <?= date("d.m.Y H:i:s", strtotime($child->date_added)); ?>
                </div>
                <a href="<?= Url::to(['/page/page/show', 'id'=>$child->id])?>" class="page-title">
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
    \app\widgets\LinkPager::widget([
        'pagination' => $pages,
    ]);
    ?>
<?php endif; ?>
<div class="pages-list-content">
    <?= $this->blocks['content'] ?>
</div>