<?php

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Products');
$this->params['breadcrumbs'][] = $this->title;
$parent_id = Yii::$app->request->get('parent_id', app\models\Category::findRootForCategoryGroup(1)->id);
?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>


<?php
$this->beginBlock('add-button');
?>
        <a href="<?= Url::toRoute(['/backend/product/edit', 'parent_id' => $parent_id, 'returnUrl' => \app\backend\components\Helper::getReturnUrl()]) ?>" class="btn btn-success">
            <?= Icon::show('plus') ?>
            <?= Yii::t('app', 'Add') ?>
        </a>
        <?= \app\backend\widgets\RemoveAllButton::widget([
            'url' => Url::toRoute(['/backend/product/remove-all', 'parent_id' => $parent_id]),
            'gridSelector' => '.grid-view',
            'htmlOptions' => [
                'class' => 'btn btn-danger pull-right'
            ],
        ]); ?>
<?php
$this->endBlock();
?>

<div class="row">
    <div class="col-md-4">
    <?=
        app\backend\widgets\JSTree::widget([
            'model' => new app\models\Category,
            'routes' => [
                'getTree' => [Url::toRoute('getTree')],
                'open' => [Url::toRoute('index')],
                'edit' => ['/backend/category/edit'],
                'delete' => ['/backend/category/delete'],
                'create' => ['/backend/category/edit'],
            ],
        ]);
?>
    </div>
    <div class="col-md-8" id="jstree-more">
<?=
    DynaGrid::widget([
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
                'class' => 'app\backend\columns\TextWrapper',
                'attribute' => 'name',
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
                'attribute' => 'active',
            ],
            [
                'class' => 'kartik\grid\EditableColumn',
                'attribute'=>'price',
                'editableOptions' => [
                    'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                    'formOptions' => [
                        'action' => 'update-editable',
                    ],
                ],
            ],
            [
                'class' => 'kartik\grid\EditableColumn',
                'attribute'=>'old_price',
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
                    'data' => [0=>'-']+\app\components\Helper::getModelMap(\app\models\Currency::className(), 'id', 'name'),
                    'inputType' => 'dropDownList',
                    'placement' => 'left',
                    'formOptions' => [
                        'action' => 'update-editable',
                    ],
                ],
                'filter' => \app\components\Helper::getModelMap(\app\models\Currency::className(), 'id', 'name'),
                'format' => 'raw',
                'value' => function($model) {
                    if ($model === null || $model->currency === null || $model->currency_id ===0) {
                        return null;
                    }
                    return \yii\helpers\Html::tag('div', $model->currency->name, ['class' => $model->currency->name]);
                },
            ],
            'sku',
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
                                'label' => Yii::t('app','Restore'),
                            ],
                            [
                                'url' => 'delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('app','Delete'),
                            ],
                        ];
                    }
                    return null;
                }
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
