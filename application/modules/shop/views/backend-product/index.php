<?php

use app\modules\shop\models\Product;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;
use app\backend\components\Helper;

$this->title = Yii::t('app', 'Products');
$this->params['breadcrumbs'][] = $this->title;
$parent_id = Yii::$app->request->get('parent_id', app\modules\shop\models\Category::findRootForCategoryGroup(1)->id);
?>

<?=app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php
$this->beginBlock('add-button');
?>
<?=\app\backend\widgets\CategoryMovementsButtons::widget([
    'url' => Url::toRoute(['categoryMovements']),
    'gridSelector' => '.grid-view',
]) ?>
<a href="<?=Url::toRoute(
    ['edit', 'parent_id' => $parent_id, 'returnUrl' => \app\backend\components\Helper::getReturnUrl()]
)?>" class="btn btn-success">
    <?=Icon::show('plus')?>
    <?=Yii::t('app', 'Add')?>
</a>
<?= \app\backend\widgets\PublishSwitchButtons::widget([
    'url' => Url::toRoute(['publish-switch']),
    'gridSelector' => '.grid-view',
]) ?>

<?= \app\modules\shop\widgets\BatchEditPriceButton::widget([
    'context' => $this->context->id,
])?>
<?=\app\backend\widgets\RemoveAllButton::widget(
    [
        'url' => Url::toRoute(['remove-all', 'parent_id' => $parent_id]),
        'gridSelector' => '.grid-view',
        'htmlOptions' => [
            'class' => 'btn btn-danger pull-right'
        ],
    ]
);?>
<?php
$this->endBlock();
?>

<div class="row">
    <div class="col-md-4">
        <?=TreeWidget::widget(
            [
                'treeDataRoute' => ['getTree'],
                'doubleClickAction' => ContextMenuHelper::actionUrl(
                    ['index', 'returnUrl' => Helper::getReturnUrl()],
                    [
                        'parent_id' => 'id',
                    ]
                ),
                'contextMenuItems' => [
                    'show' => [
                        'label' => 'Show products in category',
                        'icon' => 'fa fa-folder-open',
                        'action' => ContextMenuHelper::actionUrl(
                            ['index'],
                            [
                                'parent_id' => 'id',
                            ]
                        ),
                    ],
                    'createProduct' => [
                        'label' => 'Create product in this category',
                        'icon' => 'fa fa-plus-circle',
                        'action' => ContextMenuHelper::actionUrl(
                            ['edit', 'returnUrl' => Helper::getReturnUrl()],
                            [
                                'parent_id' => 'id',
                            ]
                        ),
                    ],
                    'edit' => [
                        'label' => 'Edit category',
                        'icon' => 'fa fa-pencil',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/shop/backend-category/edit', 'returnUrl' => Helper::getReturnUrl()]
                        ),
                    ],
                    'create' => [
                        'label' => 'Create category',
                        'icon' => 'fa fa-plus-circle',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/shop/backend-category/edit', 'returnUrl' => Helper::getReturnUrl()],
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
                                .attr('data-url', '/backend/category/delete?id=' + jQuery(node.reference[0]).data('id'))
                                .attr('data-items', '')
                                .modal('show');
                            return true;
                        }"
                        ),
                    ],
                ],
            ]
        );?>
    </div>
    <div class="col-md-8" id="jstree-more">
        <?=DynaGrid::widget(
            [
                'options' => [
                    'id' => 'Product-grid',
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
                    'slug',
                    [
                        'class' => \kartik\grid\EditableColumn::className(),
                        'attribute' => 'active',
                        'editableOptions' => [
                            'data' => [
                                0 => Yii::t('app', 'Inactive'),
                                1 => Yii::t('app', 'Active'),
                            ],
                            'inputType' => 'dropDownList',
                            'placement' => 'left',
                            'formOptions' => [
                                'action' => 'update-editable',
                            ],
                        ],
                        'filter' => [
                            0 => Yii::t('app', 'Inactive'),
                            1 => Yii::t('app', 'Active'),
                        ],
                        'format' => 'raw',
                        'value' => function (Product $model) {
                            if ($model === null || $model->active === null) {
                                return null;
                            }
                            if ($model->active === 1) {
                                $label_class = 'label-success';
                                $value = 'Active';
                            } else {
                                $value = 'Inactive';
                                $label_class = 'label-default';
                            }
                            return \yii\helpers\Html::tag(
                                'span',
                                Yii::t('app', $value),
                                ['class' => "label $label_class"]
                            );
                        },
                    ],
                    [
                        'class' => 'kartik\grid\EditableColumn',
                        'attribute' => 'price',
                        'editableOptions' => [
                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                            'formOptions' => [
                                'action' => 'update-editable',
                            ],
                        ],
                    ],
                    [
                        'class' => 'kartik\grid\EditableColumn',
                        'attribute' => 'old_price',
                        'editableOptions' => [
                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                            'formOptions' => [
                                'action' => 'update-editable',
                            ],
                        ],
                    ],
                    [
                        'attribute' => 'currency_id',
                        'class' => \kartik\grid\EditableColumn::className(),
                        'editableOptions' => [
                            'data' => [0 => '-'] + \app\components\Helper::getModelMap(
                                    \app\modules\shop\models\Currency::className(),
                                    'id',
                                    'name'
                                ),
                            'inputType' => 'dropDownList',
                            'placement' => 'left',
                            'formOptions' => [
                                'action' => 'update-editable',
                            ],
                        ],
                        'filter' => \app\components\Helper::getModelMap(
                            \app\modules\shop\models\Currency::className(),
                            'id',
                            'name'
                        ),
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model === null || $model->currency === null || $model->currency_id === 0) {
                                return null;
                            }
                            return \yii\helpers\Html::tag(
                                'div',
                                $model->currency->name,
                                ['class' => $model->currency->name]
                            );
                        },
                    ],
                    [
                        'class' => 'kartik\grid\EditableColumn',
                        'attribute' => 'sku',
                        'editableOptions' => [
                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                            'formOptions' => [
                                'action' => 'update-editable',
                            ],
                            'placement' => 'left',
                        ],
                    ],
                    'date_modified',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'buttons' => [
                            [
                                'url' => '@product',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app', 'Preview'),
                                'appendReturnUrl' => false,
                                'url_append' => '',
                                'attrs' => ['model', 'mainCategory.category_group_id'],
                                'keyParam' => null,
                            ],
                            [
                                'url' => 'edit',
                                'icon' => 'pencil',
                                'class' => 'btn-primary',
                                'label' => Yii::t('app', 'Edit'),
                            ],
                            [
                                'url' => 'clone',
                                'icon' => 'copy',
                                'class' => 'btn-success',
                                'label' => Yii::t('app', 'Clone'),
                            ],
                            [
                                'url' => 'delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('app', 'Delete'),
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ],
                        ],
                    ],
                ],
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover' => true,
                    'panel' => [
                        'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
                        'after' => $this->blocks['add-button'],
                    ],
                ]
            ]
        );?>
    </div>
</div>
