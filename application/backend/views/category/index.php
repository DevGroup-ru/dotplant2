<?php

use kartik\helpers\Html;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Categories');
if (is_object($model)) {
    $this->title = Yii::t('app', 'Categories inside category: ').'"'.$model->name.'"';

}
$parent_id = is_object($model) ? $model->id : '0';

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-4">
        <?=
            app\backend\widgets\JSTree::widget([
                'model' => new app\models\Category,
                'routes' => [
                    'getTree' => ['/backend/category/getTree', 'selected_id' => $parent_id],
                    'open' => ['/backend/category/index'],
                    'edit' => ['/backend/category/edit'],
                    'delete' => ['/backend/category/delete'],
                    'create' => ['/backend/category/edit'],
                ],
            ]);
        ?>
    </div>
    <div class="col-md-8" id="jstree-more">
        <?php
        $this->beginBlock('add-button');
        ?>
                <a href="<?= Url::to(
                    [
                        '/backend/category/edit', 
                        'parent_id'=>(is_object($model)?$model->id:0), 
                        'returnUrl' => \app\backend\components\Helper::getReturnUrl()
                    ]
                ) ?>" class="btn btn-success">
                    <?= Icon::show('plus') ?>
                    <?= Yii::t('app', 'Add') ?>
                </a>
                <?= \app\backend\widgets\RemoveAllButton::widget([
                    'url' => Url::to(
                        [
                            '/backend/category/remove-all',
                            'parent_id' => (is_object($model) ? $model->id : 0)
                        ]
                    ),
                    'gridSelector' => '.grid-view',
                    'htmlOptions' => [
                        'class' => 'btn btn-danger pull-right'
                    ],
                ]); ?>
        <?php
        $this->endBlock();
        ?>
        <?=
            DynaGrid::widget([
                'options' => [
                    'id' => 'category-grid',
                ],
                'columns' => [
                    [
                        'class' => \kartik\grid\CheckboxColumn::className(),
                        'options' => [
                            'width' => '10px',
                        ],
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
                        'buttons' => function($model, $key, $index, $parent) {
                            if (1 === $model->is_deleted) {
                                return [
                                    [
                                        'url' => 'edit',
                                        'icon' => 'pencil',
                                        'class' => 'btn-primary',
                                        'label' => Yii::t('app', 'Edit'),
                                    ],
                                    [
                                        'url' => 'restore',
                                        'icon' => 'refresh',
                                        'class' => 'btn-success',
                                        'label' => Yii::t('app', 'Restore'),
                                    ],
                                    [
                                        'url' => 'delete',
                                        'icon' => 'trash-o',
                                        'class' => 'btn-danger',
                                        'label' => Yii::t('app', 'Delete'),
                                    ],
                                ];
                            }
                            return [
                                [
                                    'url' => 'edit',
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
                            ];
                        },
                        'url_append' => '&parent_id='.(is_object($model)?$model->id:0),
                    ],
                ],
                
                'theme' => 'panel-default',
                
                'gridOptions'=>[
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover'=>true,

                    'panel'=>[
                        'heading'=>'<h3 class="panel-title">'.$this->title.'</h3>',
                        'after' => $this->blocks['add-button'],

                    ],
                    
                ]
            ]);
        ?>
    </div>
</div>



