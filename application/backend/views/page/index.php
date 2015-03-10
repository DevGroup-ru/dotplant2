<?php


/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Page $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;

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
            app\backend\widgets\JSTree::widget([
                'model' => new app\models\Page,
                'routes' => [
                    'getTree' => ['/backend/page/getTree', 'selected_id' => $parent_id],
                    'open' => ['/backend/page/index'],
                    'edit' => ['/backend/page/edit'],
                    'delete' => ['/backend/page/delete'],
                    'create' => ['/backend/page/edit'],
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
                    ['/backend/page/edit', 'parent_id' => (is_object($model) ? $model->id : 0), 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    ['class' => 'btn btn-success']
                )
                ?>
            </div>
            <?= \app\backend\widgets\RemoveAllButton::widget([
                'url' => \yii\helpers\Url::toRoute(['/backend/page/remove-all', 'parent_id' => (is_object($model) ? $model->id : 0)]),
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
                        'buttons' =>
                            function($model, $key, $index, $parent) {
                                if (1 === $model->is_deleted) {
                                    return [
                                        [
                                            'url' => 'edit',
                                            'icon' => 'pencil',
                                            'class' => 'btn-primary',
                                            'label' => Yii::t('app', 'Edit' ),
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
                                } else {
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
                                }
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
                        'after' => $this->blocks['buttonGroup'],

                    ],
                    
                ]
            ]);
        ?>
    </div>
</div>
