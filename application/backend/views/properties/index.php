<?php

use app\backend\components\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\helpers\Html;
use kartik\dynagrid\DynaGrid;
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
                'buttons' => [
                    [
                        'url' => 'group',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => 'Edit',
                    ],
                    [
                        'url' => 'delete-group',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => 'Delete',
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
                    ['/backend/properties/group'],
                    ['class' => 'btn btn-success']
                ),
            ],
            
        ]
    ]);
?>
