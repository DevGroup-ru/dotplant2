<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\seo\models\Redirect $searchModel
 */

use kartik\helpers\Html;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;
use app\backend\components\Helper;

$this->title = Yii::t('app', 'Backend menu items');
if (is_object($model)) {
    $this->title = Yii::t('app', 'Items inside item: ').'"'.$model->name.'"';

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
            'treeDataRoute' => ['/backend/backend-menu/getTree'],
            'contextMenuItems' => [
                'edit' => [
                    'label' => 'Edit',
                    'icon' => 'fa fa-pencil',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/backend/backend-menu/edit', 'returnUrl' => Helper::getReturnUrl()],
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
                        ['/backend/backend-menu/index'],
                        [
                            'parent_id' => 'id',
                        ]
                    ),
                ],
                'create' => [
                    'label' => 'Create',
                    'icon' => 'fa fa-plus-circle',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/backend/backend-menu/edit', 'returnUrl' => Helper::getReturnUrl()],
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
                                    .attr('data-url', '/backend/backend-menu/delete?id=' + jQuery(node.reference[0]).data('id'))
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
                        '/backend/backend-menu/edit',
                        'parent_id'=>(is_object($model)?$model->id:0),
                        'returnUrl' => \app\backend\components\Helper::getReturnUrl()
                    ]
                ); ?>" class="btn btn-success">
                    <?= Icon::show('plus') ?>
                    <?= Yii::t('app', 'Add') ?>
                </a>
            <?= \app\backend\widgets\RemoveAllButton::widget([
                'url' => Url::to(
                    [
                        '/backend/backend-menu/remove-all',
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
                    'id' => 'backend-menu-grid',
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
                    'route',
                    'icon',
                    'css_class',
                    'rbac_check',
                    'translation_category',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'options' => [
                            'width' => '95px',
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



