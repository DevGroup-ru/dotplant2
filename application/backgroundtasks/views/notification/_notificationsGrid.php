<?php

use app\backgroundtasks\models\NotifyMessage;
use app\backgroundtasks\widgets\notification\NewNotification;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var yii\web\View $this
 * @var string $id
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var NotifyMessage $searchModel
 */

?>

<?php Pjax::begin() ?>

<?= NewNotification::widget(
    [
        'view' => 'new_notification',
        'url' => "/background/notification/only-new-notifications?current=".time()
    ]
) ?>

<?= GridView::widget([
        'id' => $id,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'rowOptions' => function ($model, $key, $index, $grid) {
            switch ($model->result_status) {
                case NotifyMessage::STATUS_SUCCESS:
                    return ['class' => 'success'];
                case NotifyMessage::STATUS_FAULT:
                    return ['class' => 'danger'];

                default:
                    return [];
            }
        },
        'columns' => [
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
                'visible' => false,
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'ts',
                'options' => [
                    'width' => '200px',
                ],
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'name',
                'value' => function ($data) {
                        return isset($data->task) ? $data->task->name : '(not set)';
                },
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'username',
                'value' => function ($data) {
                        return isset($data->task->initiatorUser) ? $data->task->initiatorUser->username : '(not set)';
                },
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'result_status',
                'filter' => NotifyMessage::getStatuses(),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'options' => [
                    'width' => '25px',
                ],
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped table-condensed table-hover',
        ]
    ]); ?>

<?php Pjax::end() ?>
