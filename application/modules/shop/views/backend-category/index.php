<?php

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;
use app\backend\components\Helper;

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
                'treeDataRoute' => ['getTree', 'selected_id' => $parent_id],
                'changeParentAction' => 'move',
                'reorderAction' => 'reorder',
                'doubleClickAction' => ContextMenuHelper::actionUrl(
                    ['edit', 'returnUrl' => Helper::getReturnUrl()]
                ),
                'contextMenuItems' => [
                    'edit' => [
                        'label' => 'Edit',
                        'icon' => 'fa fa-pencil',
                        'action' => ContextMenuHelper::actionUrl(
                            ['edit', 'returnUrl' => Helper::getReturnUrl()]
                        ),
                    ],
                    'open' => [
                        'label' => 'Open',
                        'icon' => 'fa fa-folder-open',
                        'action' => ContextMenuHelper::actionUrl(
                            ['index'],
                            [
                                'parent_id' => 'id',
                            ]
                        ),
                    ],
                    'create' => [
                        'label' => 'Create',
                        'icon' => 'fa fa-plus-circle',
                        'action' => ContextMenuHelper::actionUrl(
                            ['edit', 'returnUrl' => Helper::getReturnUrl()],
                            [
                                'parent_id' => 'id',
                            ]
                        ),
                    ],
                    'delete' => [
                        'label' => 'Delete',
                        'icon' => 'fa fa-trash-o',
                        'action' => new \yii\web\JsExpression(
                            "function(node) {
                                jQuery('#delete-category-confirmation')
                                    .attr('data-url', '/shop/backend-category/delete?id=' + jQuery(node.reference[0]).data('id'))
                                    .attr('data-items', '')
                                    .modal('show');
                                return true;
                            }"
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
                        'edit',
                        'parent_id'=>(is_object($model)?$model->id:0), 
                        'returnUrl' => \app\backend\components\Helper::getReturnUrl()
                    ]
                ) ?>" class="btn btn-success">
                    <?= Icon::show('plus') ?>
                    <?= Yii::t('app', 'Add') ?>
                </a>
                <?= \app\modules\shop\widgets\BatchEditPriceButton::widget([
                    'context' => $this->context->id,
                ])?>
                <?= \app\backend\widgets\RemoveAllButton::widget([
                    'url' => Url::to(
                        [
                            'remove-all',
                            'parent_id' => (is_object($model) ? $model->id : 0)
                        ]
                    ),
                    'gridSelector' => '.grid-view',
                    'modalSelector' => '#delete-category-confirmation',
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
                        'class' => 'yii\grid\DataColumn',
                        'attribute' => 'name',
                    ],
                    'title',
                    'slug',
                    'date_modified',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'buttons' => [
                            [
                                'url' => '@category',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app', 'Preview'),
                                'appendReturnUrl' => false,
                                'url_append' => '',
                                'keyParam' => 'category_id',
                                'attrs' => ['category_group_id'],
                            ],
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
                                'options' => [
                                    'data-action' => 'delete-category',
                                ],
                            ],
                        ],
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
