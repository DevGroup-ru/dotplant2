<?php

use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $id
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\seo\models\Redirect $searchModel
 */

?>

<?php Pjax::begin() ?>

<?= GridView::widget([
    'id' => $id,
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "{items}\n{summary}\n{pager}",
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'options' => [
                'width' => '10px',
            ],
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'id',
            'options' => [
                'width' => '60px',
            ],
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'type',
            'filter' => \app\seo\models\Redirect::getTypes(),
        ],
        'from',
        'to',
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'active',
            'filter' => [
                0 => 'false',
                1 => 'true',
            ],
        ],
        [
            'class' => 'app\components\ActionColumn',
            'urlCreator' => function ($action, $model, $key, $index) {
                $params = is_array($key) ? $key : ['id' => (string)$key];
                $action .= '-redirect';
                $params[0] = $this->context->id ? $this->context->id . '/' . $action : $action;
                return Url::toRoute($params);
            },
            'template' => '{update} {delete}',
            'options' => [
                'width' => '60px',
            ]
        ],
    ],
    'tableOptions' => [
        'class' => 'table table-striped table-condensed table-hover',
    ]
]); ?>

<?php Pjax::end() ?>
