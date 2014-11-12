<?php

use yii\helpers\Url;

/**
     * @var \app\models\Search $model
     * @var \yii\web\View $this
     */
    $this->title = Yii::t('app', 'Search') . ': ' . $model->q;
?>
<h1 style="font-size: 24px; font-weight: normal;"><?= Yii::t('shop', 'Search results') ?></h1>
<div id="pages-list">
    <div style="text-align: center;"><img src="/img/ajax-loader.gif" alt="" /></div>
</div>
<h3 style="font-size: 16px; font-weight: bold;"><?= Yii::t('shop', 'Products and services') ?></h3>
<div id="products-list">
    <div style="text-align: center;"><img src="/img/ajax-loader.gif" alt="" /></div>
</div>
<script>
/*global $:false, bootbox, console, alert, document */
"use strict";



$(function() {
    // jQuery('#products-list').load('<?= \yii\helpers\Url::to(['/product/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]); ?>');
    // jQuery('#pages-list').load('<?= \yii\helpers\Url::to(['/page/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]); ?>');
    
    function searchCallback(data, $element) {
        $element.empty().append($(data.view));
        if (data.totalCount > 0) {
            $(".no-results").hide();
        }
    }
    $.ajax({
        url: "<?= Url::to(['/product/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]) ?>",
    }).done(function(data) {
        return searchCallback(data, $('#products-list'));
    });
    $.ajax({
        url: "<?= Url::to(['/page/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]) ?>",
    }).done(function(data) {
        return searchCallback(data, $('#pages-list'));
    });

    $('#products-list').on('click', '.pagination a', function() {
        $('#products-list').load($(this).attr('href'));
        return false;
    });
});
</script>