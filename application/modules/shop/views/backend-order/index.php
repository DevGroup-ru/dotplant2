<?php

use kartik\dynagrid\DynaGrid;
use yii\helpers\Html;

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $managers string[]
 * @var $orderStages \app\modules\shop\models\OrderStage[]
 * @var $paymentTypes \app\modules\shop\models\PaymentType[]
 * @var $searchModel app\components\SearchModel
 * @var $shippingOptions \app\modules\shop\models\ShippingOption[]
 * @var $this yii\web\View
 */

$this->title = Yii::t('app', 'Orders');
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
                    'after' => Html::a(
                        \kartik\icons\Icon::show('plus') . Yii::t('app', 'Add'),
                        ['create'],
                        ['class' => 'btn btn-success']
                    ),
                ],
                'rowOptions' => function ($model, $key, $index, $grid) {
                    if ($model->is_deleted) {
                        return [
                            'class' => 'danger',
                        ];
                    }
                    return [];
                },
            ],
            'columns' => [
                [
                    'attribute' => 'id',
                ],
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
                    'label' => Yii::t('app', 'User'),
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
                    'attribute' => 'order_stage_id',
                    'filter' => $orderStages,
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->stage === null) {
                            return null;
                        }
                        return Html::tag(
                            'span',
                            $model->stage->name_frontend,
                            [
                                'class' => ['order_stage', 'order_stage' . $model->stage->id]
                            ]
                        );
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
                    'buttons' => function ($model, $key, $index, $parent) {
                        $result = [
                            [
                                'url' => 'view',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app', 'View'),
                            ],
                        ];
                        if (intval(Yii::$app->getModule('shop')->deleteOrdersAbility) === 1 && $model->is_deleted == 0) {
                            $result[] = [
                                'url' => 'delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('app', 'Delete'),
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ];
                        }
                        return $result;
                    },
                ],
            ],
        ]
    );
    ?>
</div>
