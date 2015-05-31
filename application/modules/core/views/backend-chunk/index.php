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

$this->title = Yii::t('app', 'Content Blocks');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

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
    'gridOptions'=>[
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover' => true,
        'panel' => [

            'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
            'after' => Html::a(
                    Icon::show('plus') . Yii::t('app', 'Add'),
                    ['/core/backend-chunk/edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    ['class' => 'btn btn-success']
                ) . \app\backend\widgets\RemoveAllButton::widget([
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
