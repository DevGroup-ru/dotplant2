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