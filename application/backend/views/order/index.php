<?php

use kartik\dynagrid\DynaGrid;
use yii\helpers\Html;

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $managers string[]
 * @var $orderStatuses \app\models\OrderStatus[]
 * @var $paymentTypes \app\models\PaymentType[]
 * @var $searchModel app\components\SearchModel
 * @var $shippingOptions \app\models\ShippingOption[]
 * @var $this yii\web\View
 */

$this->title = Yii::t('shop', 'Orders');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="order-index">
    <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'orders-grid',
                ],
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover' => true,
                    'panel' => [
                        'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    ],
                ],
                'columns' => [
                    'id',
                    [
                        'attribute' => 'manager_id',
                        'filter' => $managers,
                        'value' => function ($model, $key, $index, $column) {
                            if ($model === null || $model->manager === null) {
                                return null;
                            }
                            return $model->manager->username;
                        },
                    ],
                    [
                        'attribute' => 'user_username',
                        'label' => Yii::t('shop', 'User'),
                        'value' => function ($model, $key, $index, $column) {
                            if ($model === null || $model->user === null) {
                                return null;
                            }
                            return $model->user->username;
                        },
                    ],
                    'start_date',
                    'end_date',
                    [
                        'attribute' => 'order_status_id',
                        'class' => \kartik\grid\EditableColumn::className(),
                        'editableOptions' => [
                            'data' => $orderStatuses,
                            'inputType' => 'dropDownList',
                            'formOptions' => [
                                'action' => 'update-status',
                            ],
                        ],
                        'filter' => $orderStatuses,
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            if ($model === null || $model->status === null) {
                                return null;
                            }
                            return Html::tag('div', $model->status->short_title, ['class' => $model->status->label]);
                        },
                    ],
                    [
                        'attribute' => 'shipping_option_id',
                        'filter' => $shippingOptions,
                        'value' => function ($model, $key, $index, $column) {
                            if ($model === null || $model->shippingOption === null) {
                                return null;
                            }
                            return $model->shippingOption->name;
                        },
                    ],
                    [
                        'attribute' => 'payment_type_id',
                        'filter' => $paymentTypes,
                        'value' => function ($model, $key, $index, $column) {
                            if ($model === null || $model->paymentType === null) {
                                return null;
                            }
                            return $model->paymentType->name;
                        },
                    ],
                    'items_count',
                    'total_price',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'buttons' => [
                            [
                                'url' => 'view',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app','View'),
                            ],
                        ],
                    ],
                ],
            ]
        );
    ?>
</div>
