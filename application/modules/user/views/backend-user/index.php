<?php

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \app\components\SearchModel
 * @var $this \yii\web\View
 */

use app\backend\components\ActionColumn;
use app\modules\user\models\User;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-index">
    <?=
        DynaGrid::widget([
            'options' => [
                'id' => 'users-grid',
            ],
            'columns' => [
                [
                    'class' => \kartik\grid\CheckboxColumn::className(),
                    'options' => [
                        'width' => '10px',
                    ],
                ],
                'id',
                'username',
                'email:email',
                [
                    'attribute' => 'status',
                    'filter' => User::getStatuses(),
                    'value' => function ($data) {
                        return isset(User::getStatuses()[$data->status])
                            ? User::getStatuses()[$data->status]
                            : $data->status;
                    },
                ],
                'create_time:datetime',
                [
                    'class' => ActionColumn::className(),
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
                            'options' => [
                                'data-action' => 'delete',
                            ],
                            'label' => Yii::t('app', 'Delete'),
                        ],
                    ],
                ],
            ],
            'theme' => 'panel-default',
            'gridOptions'=>[
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover' => true,
                'panel' => [
                    'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    'after' => Html::a(
                        Icon::show('plus') . Yii::t('app', 'Add'),
                        ['/user/backend-user/update'],
                        ['class' => 'btn btn-success']
                    ) . \app\backend\widgets\RemoveAllButton::widget([
                        'url' => '/user/backend-user/remove-all',
                        'gridSelector' => '.grid-view',
                        'htmlOptions' => [
                            'class' => 'btn btn-danger pull-right'
                        ],
                    ]),

                ],
            ]
        ]);
    ?>
</div>