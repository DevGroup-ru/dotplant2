<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveproductProvider $productProvider
 * @var \app\models\Form $productModel
 */



$this->title = Yii::t('app', 'Trash');
$this->params['breadcrumbs'][] = $this->title;

?>




<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>


<?= $this->render(
    '_table',
    [
        'name' => Yii::t('app', 'Orders Trash'),
        'objectModel' => $orderModel,
        'objectProvider' => $orderProvider,
        'colums' => [
            [
                'class' => 'app\backend\columns\TextWrapper',
                'attribute' => 'id',
                'callback_wrapper' => function($content, $model, $key, $index, $parent) {
                    if (1 === $model->is_deleted) {
                        $content = '<div class="is_deleted"><span class="fa fa-trash-o"></span>'.$content.'</div>';
                    }

                    return $content;
                }
            ],
            [
                'attribute' => 'manager_id',
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
                'attribute' => 'order_status_id',
                'class' => \kartik\grid\EditableColumn::className(),
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if ($model === null || $model->status === null) {
                        return null;
                    }
                    return \yii\helpers\Html::tag('div', $model->status->short_title, ['class' => $model->status->label]);
                },
            ],
            [
                'attribute' => 'shipping_option_id',
                'value' => function ($model, $key, $index, $column) {
                    if ($model === null || $model->shippingOption === null) {
                        return null;
                    }
                    return $model->shippingOption->name;
                },
            ],
            [
                'attribute' => 'payment_type_id',
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
                'controller' => 'order',
                'buttons' => [
                    [
                        'url' => 'restore',
                        'icon' => 'refresh',
                        'class' => 'btn-success',
                        'label' => 'Restore',
                    ],
                    [
                        'url' => 'delete',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => 'Delete',
                    ],
                ]
            ],

        ]
    ]
); ?>