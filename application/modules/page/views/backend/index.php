<?php


/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Page $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;

$this->title = Yii::t('app', 'Pages');
if (is_object($model)) {
    $this->title = Yii::t('app', 'Pages inside page: ') . '"' . $model->breadcrumbs_label . '"';

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
                ['/page/backend/edit']
            ),
            'contextMenuItems' => [
                'edit' => [
                    'label' => 'Edit',
                    'icon' => 'fa fa-pencil',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/page/backend/edit']
                    ),
                ],
                'show' => [
                    'label' => 'Show pages inside this page',
                    'icon' => 'fa fa-folder-o',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/page/backend/index'],
                        [
                            'parent_id' => 'id',
                        ]
                    ),
                ],
                'create' => [
                    'label' => 'Create',
                    'icon' => 'fa fa-plus-circle',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/page/backend/edit'],
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
                                .attr('data-url', '/page/backend/delete?id=' + jQuery(node.reference[0]).data('id'))
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

        <?php $this->beginBlock('buttonGroup'); ?>
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group">
                <?=
                \yii\helpers\Html::a(
                    Icon::show('plus') . Yii::t('app', 'Add'),
                    ['/page/backend/edit', 'parent_id' => (is_object($model) ? $model->id : 0), 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    ['class' => 'btn btn-success']
                )
                ?>
            </div>
            <?= \app\backend\widgets\RemoveAllButton::widget([
                'url' => \yii\helpers\Url::toRoute(['/page/backend/remove-all', 'parent_id' => (is_object($model) ? $model->id : 0)]),
                'gridSelector' => '.grid-view',
                'htmlOptions' => [
                    'class' => 'btn btn-danger pull-right'
                ],
            ]); ?>
        </div>
        <?php $this->endBlock(); ?>

        <?=
            DynaGrid::widget([
                'options' => [
                    'id' => 'page-grid',
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
                        'attribute' => 'title',
                    ],

                    'slug',
                    [
                        'class' => 'app\backend\columns\BooleanStatus',
                        'attribute' => 'published',
                    ],
                    'date_modified',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'buttons' => [
                            [
                                'url' => '@article',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app', 'Preview'),
                                'appendReturnUrl' => false,
                                'url_append' => '',
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
                        'after' => $this->blocks['buttonGroup'],

                    ],
                    
                ]
            ]);
        ?>
    </div>
</div>
