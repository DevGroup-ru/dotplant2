<?php

use yii\helpers\Url;

/**
     * @var \app\models\Search $model
     * @var \yii\web\View $this
     */
    $this->title = Yii::t('app', 'Search') . ': ' . $model->q;
?>
<h1 style="font-size: 24px; font-weight: normal;"><?= Yii::t('app', 'Search results') ?></h1>
<div id="pages-list">
    <div style="text-align: center;"><img src="/img/ajax-loader.gif" alt="" /></div>
</div>
<h3 style="font-size: 16px; font-weight: bold;"><?= Yii::t('app', 'Products and services') ?></h3>
<div id="products-list">
    <div style="text-align: center;"><img src="/img/ajax-loader.gif" alt="" /></div>
</div>
<script>
/*global $:false, bootbox, console, alert, document */
"use strict";



$(function() {
    function load(url, $element) {
        $.ajax({
            url: url
        }).done(function (data) {
            $element.empty().append($(data.view));
            if (data.totalCount > 0) {
                $(".no-results").hide();
            }
        });
    }

    load("<?= Url::to(['/page/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]) ?>", $('#pages-list'));
    load("<?= Url::to(['/product/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]) ?>", $('#products-list'));

    $('#products-list').on('click', '.pagination a', function() {
        load($(this).attr('href'), $('#products-list'));
        return false;
    });

    $('#pages-list').on('click', '.pagination a', function() {
        load($(this).attr('href'), $('#pages-list'));
        return false;
    });
});
</script>