<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 * @var Int $parent_id
 */

use app\backend\components\ActionColumn;
use app\backend\components\Helper;
use app\modules\core\models\ContentBlockGroup;
use devgroup\JsTreeWidget\ContextMenuHelper;
use devgroup\JsTreeWidget\TreeWidget;
use kartik\dynagrid\DynaGrid;
use kartik\grid\BooleanColumn;
use kartik\helpers\Html;
use kartik\icons\Icon;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Content Blocks');
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
                ['/core/backend-chunk-group/update', 'returnUrl' => Helper::getReturnUrl()]
            ),
            'contextMenuItems' => [
                'edit' => [
                    'label' => 'Edit',
                    'icon' => 'fa fa-pencil',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/core/backend-chunk-group/update', 'returnUrl' => Helper::getReturnUrl()]
                    ),
                ],
                'open' => [
                    'label' => 'Open',
                    'icon' => 'fa fa-folder-open',
                    'action' => ContextMenuHelper::actionUrl(
                        ['index'],
                        [
                            'group_id' => 'id',
                        ]
                    ),
                ],
                'create' => [
                    'label' => 'Create',
                    'icon' => 'fa fa-plus-circle',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/core/backend-chunk-group/create', 'returnUrl' => Helper::getReturnUrl()],
                        [
                            'parent_id' => 'id',
                            'returnUrl' => Helper::getReturnUrl()
                        ]
                    ),
                ],
                'delete' => [
                    'label' => 'Delete',
                    'icon' => 'fa fa-trash-o',
                    'action' => new \yii\web\JsExpression(
                        "function(node) {
                                jQuery('#confirm_delete')
                                    .modal('toggle');

                                jQuery('#confirm_delete form').attr('action', '/core/backend-chunk-group/delete?id=' + jQuery(node.reference[0]).data('id'))
                                return true;
                            }"
                    ),
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-8">
        <?=
        DynaGrid::widget([
            'options' => [
                'id' => 'backend-chunk-grid',
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
                'name',
                'key',
                [
                    'class' => BooleanColumn::className(),
                    'attribute' => 'preload',
                ],
                [
                    'attribute' => 'group',
                    'value' => 'group.name'
                ],
                [
                    'class' => ActionColumn::className(),
                    'options' => [
                        'width' => '95px',
                    ],
                    'buttons' => [
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
                    ],
                ],
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover' => true,
                'panel' => [

                    'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    'after' => Html::a(
                            Icon::show('plus') . Yii::t('app', 'Add'),
                            [
                                '/core/backend-chunk/edit',
                                'group_id' => $parent_id,
                                'returnUrl' => \app\backend\components\Helper::getReturnUrl()
                            ],
                            ['class' => 'btn btn-success']
                        ) . '&nbsp' .
                        Html::a(
                            Yii::t('app', 'Show all'),
                            [
                                '/core/backend-chunk/index'
                            ],
                            ['class' => 'btn btn-warning']
                        ) .
                        \app\backend\widgets\RemoveAllButton::widget([
                            'url' => '/core/backend-chunk/remove-all',
                            'gridSelector' => '.grid-view',
                            'htmlOptions' => [
                                'class' => 'btn btn-danger pull-right'
                            ],
                        ]),
                ],

            ]
        ]);
        ?>
    </div>
</div>

<?php Modal::begin([
    'id' => 'confirm_delete',
    'header' => Yii::t('app', 'Confirm delete item')
]); ?>

<?php $form = ActiveForm::begin([
    'options' => [
        'action' => Url::to(['/core/backend-chunk-group/delete', 'returnUrl' => Helper::getReturnUrl()])
    ]
]); ?>
<?php
$contentBlockModel = new ContentBlockGroup();
?>

<?= $form->field($contentBlockModel, 'id')->hiddenInput()->label(false) ?>
<?= $form->field($contentBlockModel, 'deleteMethod')->dropDownList([
    ContentBlockGroup::DELETE_METHOD_PARENT_ROOT => Yii::t('app', 'Move child chunks to root group'),
    ContentBlockGroup::DELETE_METHOD_ALL => Yii::t('app', 'Delete all child chunks'),
]) ?>

<?= Html::submitButton(Yii::t('app', 'Delete'), ['class' => ['btn btn-danger']]); ?>


<?php ActiveForm::end(); ?>
<?php Modal::end(); ?>
