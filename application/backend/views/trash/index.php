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
        'name' => Yii::t('app', 'Categories Trash'),
        'objectModel' => $categoryModel,
        'objectProvider' => $categoryProvider,
        'colums' => [
            [
                'class' => \app\backend\columns\CheckboxColumn::className(),
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
            ],
            [
                'class' => 'app\backend\columns\TextWrapper',
                'attribute' => 'name',
                'callback_wrapper' => function($content, $model, $key, $index, $parent) {
                    if (1 === $model->is_deleted) {
                        $content = '<div class="is_deleted"><span class="fa fa-trash-o"></span>'.$content.'</div>';
                    }

                    return $content;
                }
            ],
            'title',
            'slug',
            [
                'class' => 'app\backend\components\ActionColumn',
                'controller' => 'category',
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


<?= $this->render(
    '_table',
    [
        'name' => Yii::t('app', 'Products Trash'),
        'objectModel' => $productModel,
        'objectProvider' => $productProvider,
       'colums' => [
           [
               'class' => \app\backend\columns\CheckboxColumn::className(),
           ],
           [
               'class' => 'yii\grid\DataColumn',
               'attribute' => 'id',
           ],
           [
               'class' => 'app\backend\columns\TextWrapper',
               'attribute' => 'name',
               'callback_wrapper' => function ($content, $model, $key, $index, $parent) {
                   if (1 === $model->is_deleted) {
                       $content = '<div class="is_deleted"><span class="fa fa-trash-o"></span>' . $content . '</div>';
                   }

                   return $content;
               }
           ],
           'slug',
           [
               'class' => 'app\backend\columns\BooleanStatus',
               'attribute' => 'active',
           ],
           'price',
           'old_price',
           [
               'class' => 'app\backend\components\ActionColumn',
               'controller' => 'product',
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


<?= $this->render(
    '_table',
    [
        'name' => Yii::t('app', 'Pages Trash'),
        'objectModel' => $pageModel,
        'objectProvider' => $pageProvider,
        'colums' => [
            [
                'class' => \app\backend\columns\CheckboxColumn::className(),
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
            ],
            [
                'class' => 'app\backend\columns\TextWrapper',
                'attribute' => 'title',
                'callback_wrapper' => function($content, $model, $key, $index, $parent) {
                    if (1 === $model->is_deleted) {
                        $content = '<div class="is_deleted"><span class="fa fa-trash-o"></span>'.$content.'</div>';
                    }

                    return $content;
                }
            ],

            'slug',
            [
                'class' => 'app\backend\columns\BooleanStatus',
                'attribute' => 'published',
            ],
            [
                'class' => 'app\backend\components\ActionColumn',
                'controller' => 'page',
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