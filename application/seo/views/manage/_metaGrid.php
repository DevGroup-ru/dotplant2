<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/**
 * @var $id
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\seo\models\Meta $searchModel
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
        'key',
        'name',
        'content',
        [
            'class' => 'app\components\ActionColumn',
            'urlCreator' => function ($action, $model, $key, $index) {
                $params = is_array($key) ? $key : ['id' => (string)$key];
                $action .= '-meta';
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
