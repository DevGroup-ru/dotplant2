<?php

use yii\helpers\Html;
use kartik\dynagrid\DynaGrid;
use app\backend\components\ActionColumn;
use kartik\icons\Icon;

/* @var $this yii\web\View */
/* @var $searchModel app\components\SearchModel */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('shop', 'Categories groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-group-index">

    <?=
    DynaGrid::widget(
        [
            'options' => [
                'id' => 'order-statuses-grid',
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
                        ['/backend/category-group/update', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                        ['class' => 'btn btn-success']
                    ),
                ],
            ],
            'columns' => [
                'id',
                'name',
                [
                    'class' => ActionColumn::className(),
                    'buttons' => [
                        [
                            'url' => 'update',
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
        ]
    );
    ?>

</div>
