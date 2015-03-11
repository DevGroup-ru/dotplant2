<?php

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\grid\BooleanColumn;
use kartik\helpers\Html;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Order Statuses');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="order-status-index">
    <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'order-statuses-grid',
                ],
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover' => true,
                    'panel' => [
                        'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                        'after' => Html::a(
                            Icon::show('plus') . Yii::t('app', 'Add'),
                            ['/backend/order-status/update', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                            ['class' => 'btn btn-success']
                        ) . \app\backend\widgets\RemoveAllButton::widget([
                            'url' => '/backend/order-status/remove-all',
                            'gridSelector' => '.grid-view',
                            'htmlOptions' => [
                                'class' => 'btn btn-danger pull-right'
                            ],
                        ]),
                    ],
                ],
                'columns' => [
                    [
                        'class' => \kartik\grid\CheckboxColumn::className(),
                        'options' => [
                            'width' => '10px',
                        ],
                    ],
                    'id',
                    'title',
                    'short_title',
                    [
                        'attribute' => 'label',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model->label, ['class' => $model->label]);
                        },
                    ],
                    'label',
                    'external_id',
                    [
                        'class' => BooleanColumn::className(),
                        'attribute' => 'edit_allowed',
                    ],
                    [
                        'class' => BooleanColumn::className(),
                        'attribute' => 'not_deletable',
                    ],
                    [
                        'class' => ActionColumn::className(),
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
            ]
        );
    ?>
</div>
