<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;
use app\models\View;


?>
<div class="widget-pages-list">
    <ul class="media-list pages-list">
        <?php foreach ($children as $child): ?>
            <li class="media">
                <a href="<?= Url::to(['/page/show', 'id'=>$child->id])?>" class="page-title">
                    <?= Yii::t('shop', $child->title) ?>
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
    <a href="<?= Url::to(['/page/list', 'id'=>$model->id]); ?>" class="btn btn-xs btn-default btn-read-more">
        <?= Yii::t('shop', $more_pages_label) ?> <?= Icon::show('arrow-right') ?>
    </a>
    <?php endif; ?>
</div>