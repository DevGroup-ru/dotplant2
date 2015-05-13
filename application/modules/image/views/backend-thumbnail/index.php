<?php
use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;

$this->title = Yii::t('app', 'Create thumbnail');


$this->params['breadcrumbs'][] = $this->title;
echo Html::beginTag('section', ['id' => 'widget-grid']);
echo Html::beginTag('div', ['class' => 'row']);
echo Html::beginTag('article', ['class' => 'col-xs-12 col-sm-6 col-md-6 col-lg-8']);
BackendWidget::begin(['title' => '']);
echo Html::beginTag('div', ['class' => 'form-group no-margin']);
echo Html::a(
    Yii::t('app', 'Create thumbnails for all images'),
    ['recreate', 'param' => 'all'],
    ['class' => 'btn btn-success']
);
echo Html::a(
    Yii::t('app', 'Create thumbnails for images ids from config'),
    ['recreate', 'param' => 'config'],
    ['class' => 'btn btn-success col-md-offset-1']
);
echo Html::endTag('div');
BackendWidget::end();
echo Html::endTag('article');
echo Html::endTag('div');
echo Html::endTag('section');