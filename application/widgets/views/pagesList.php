<?php

use kartik\icons\Icon;
use yii\helpers\Url;


?>
<div class="widget-pages-list">
    <ul class="media-list pages-list">
        <?php foreach ($children as $child): ?>
            <li class="media">
                <a href="<?= Url::to(['/page/page/show', 'id'=>$child->id])?>" class="page-title">
                    <?= \yii\helpers\Html::encode($child->title) ?>
                </a>
                <div class="page-date_added label label-default">
                    <?= Icon::show('calendar') ?>
                    <?= date("d.m.Y H:i:s", strtotime($child->date_added)); ?>
                </div>
                
                <div class="media-body">
                    
                    <?= $child->announce ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (!empty($more_pages_label)) : ?>
    <a href="<?= Url::to(['/page/page/list', 'id'=>$model->id]); ?>" class="btn btn-xs btn-default btn-read-more">
        <?= Yii::t('app', $more_pages_label) ?> <?= Icon::show('arrow-right') ?>
    </a>
    <?php endif; ?>
</div>