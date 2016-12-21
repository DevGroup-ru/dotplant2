<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;
use app\backend\components\Helper;


$this->title = Yii::t('app', 'Navigations');
if (is_object($model)) {
    $this->title = Yii::t('app', 'Navigation: ').'"'.$model->name.'"';

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
                ['edit', 'returnUrl' => Helper::getReturnUrl()],
                [
                    'parent_id' => 'id',
                    'id' => 'id'
                ]
            ),
            'contextMenuItems' => [
                'edit' => [
                    'label' => 'Edit',
                    'icon' => 'fa fa-pencil',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/backend/navigation/edit', 'returnUrl' => Helper::getReturnUrl()],
                        [
                            'parent_id' => 'parent_id',
                            'id' => 'id'
                        ]
                    ),
                ],
                'open' => [
                    'label' => 'Open',
                    'icon' => 'fa fa-folder-open',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/backend/navigation/index'],
                        [
                            'parent_id' => 'id',
                        ]
                    ),
                ],
                'create' => [
                    'label' => 'Create',
                    'icon' => 'fa fa-plus-circle',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/backend/navigation/edit', 'returnUrl' => Helper::getReturnUrl()],
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
                                jQuery('#delete-confirmation')
                                    .attr('data-url', '/backend/navigation/delete?id=' + jQuery(node.reference[0]).data('id'))
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
    <div class="col-md-8">
        <?php
        $this->beginBlock('add-button');
        ?>
                <?= \yii\helpers\Html::a(
                    Icon::show('plus') . " " . Yii::t('app', 'Add'),
                    ['/backend/navigation/edit', 'parent_id' => (is_object($model) ? $model->id : 0), 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    [
                        'class' => 'btn btn-success',
                    ]
                ) ?>
                <?= \app\backend\widgets\RemoveAllButton::widget([
                    'url' => \yii\helpers\Url::to([
                        '/backend/navigation/remove-all',
                        'parent_id' => $parent_id,
                    ]),
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
                    'id' => 'nav-grid',
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
                        'value' => function (\app\widgets\navigation\models\Navigation $model) {
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
                    'name',
                    'url',
                    'route',
                    'route_params',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'url_append' => "&parent_id={$parent_id}",
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
