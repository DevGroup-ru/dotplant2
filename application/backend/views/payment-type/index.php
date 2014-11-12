<?php

use app\backend\components\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\helpers\Html;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Payment Types');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-type-index">
    <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'payment-types-grid',
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
                            ['/backend/payment-type/update'],
                            ['class' => 'btn btn-success']
                        ),
                    ],
                ],
                'columns' => [
                    'id',
                    'name',
                    'class',
                    'commission',
                    [
                        'class' => BooleanColumn::className(),
                        'attribute' => 'active',
                    ],
                    [
                        'class' => BooleanColumn::className(),
                        'attribute' => 'payment_available',
                    ],
                    'sort',
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
