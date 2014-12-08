<?php

use app\backgroundtasks\models\Task;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var yii\web\View $this
 * @var string $id
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var Task $searchModel
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
        'name',
        'description',
        'action',
        'params',
        'cron_expression',
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'username',
            'value' => function ($data) {
                return isset($data->initiatorUser) ? $data->initiatorUser->username : 'undefined';
            }
        ],
        'ts',
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'status',
            'filter' => Task::getStatuses(Task::TYPE_REPEAT),
            'options' => [
                'width' => '130px',
            ],
        ],
        [
            'class' => 'app\backend\components\ActionColumn',
            'options' => [
                'width' => '95px',
            ],
            'buttons' => [
                [
                    'url' => 'update',
                    'icon' => 'pencil',
                    'class' => 'btn-primary',
                    'label' => Yii::t('app', 'Edit'),
                ],
                [
                    'url' => 'delete',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('app', 'Delete'),
                ],
            ],
        ],
    ],
    'tableOptions' => [
        'class' => 'table table-striped table-condensed table-hover',
    ]
]); ?>

<?php Pjax::end() ?>
