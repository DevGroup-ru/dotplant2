<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\grid\BooleanColumn;
use kartik\helpers\Html;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Property groups');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?=
    DynaGrid::widget([
        'options' => [
            'id' => 'properties-grid',
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
                'attribute' => 'object_id',
                'filter' => app\models\Object::getSelectArray(),
                'value' => function ($model, $key, $index, $widget) {
                    $array = app\models\Object::getSelectArray();
                    return $array[$model->object_id];
                }
            ],
            'name',
            [
                'class' => BooleanColumn::className(),
                'attribute' => 'is_internal',
            ],
            [
                'class' => BooleanColumn::className(),
                'attribute' => 'hidden_group_title',
            ],
            [
                'class' => ActionColumn::className(),
                'options' => [
                    'width' => '95px',
                ],
                'buttons' => [
                    [
                        'url' => 'group',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => Yii::t('app', 'Edit'),
                    ],
                    [
                        'url' => 'delete-group',
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
        'gridOptions'=>[
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [

                'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                'after' => Html::a(
                    Icon::show('plus') . Yii::t('app', 'Add'),
                    ['/backend/properties/group', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    ['class' => 'btn btn-success']
                ) . \app\backend\widgets\RemoveAllButton::widget([
                    'url' => '/backend/properties/remove-all-groups',
                    'gridSelector' => '.grid-view',
                    'htmlOptions' => [
                        'class' => 'btn btn-danger pull-right'
                    ],
                ]),
            ],
            
        ]
    ]);
?>
