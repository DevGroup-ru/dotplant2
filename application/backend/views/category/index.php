<?php

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;

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
            TreeWidget::widget([
                'treeDataRoute' => ['/backend/category/getTree', 'selected_id' => $parent_id],
                'changeParentAction' => '/backend/category/move',
                'reorderAction' => '/backend/category/reorder',
                'contextMenuItems' => [
                    'edit' => [
                        'label' => 'Edit',
                        'icon' => 'fa fa-pencil',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/backend/category/edit']
                        ),
                    ],
                    'open' => [
                        'label' => 'Open',
                        'icon' => 'fa fa-folder-open',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/backend/category/index'],
                            [
                                'parent_id',
                            ]
                        ),
                    ],
                    'create' => [
                        'label' => 'Create',
                        'icon' => 'fa fa-plus-circle',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/backend/category/edit'],
                            [
                                'parent_id',
                            ]
                        ),
                    ],
                    'delete' => [
                        'label' => 'Delete',
                        'icon' => 'fa fa-trash-o',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/backend/category/delete']
                        ),
                    ],
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



