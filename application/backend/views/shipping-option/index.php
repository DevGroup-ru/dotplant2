<?php

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\grid\BooleanColumn;
use kartik\helpers\Html;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Shipping Options');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="shipping-option-index">
    <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'shipping-options-grid',
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
                            ['/backend/shipping-option/update', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                            ['class' => 'btn btn-success']
                        ) . \app\backend\widgets\RemoveAllButton::widget([
                            'url' => '/backend/shipping-option/remove-all',
                            'gridSelector' => '.grid-view',
                            'htmlOptions' => [
                                'class' => 'btn btn-danger pull-right'
                            ],
                        ]),
                    ],
                ],
                'columns' => [
                    [
                        'class' => \kartik\grid\CheckboxColumn::className(),
                        'options' => [
                            'width' => '10px',
                        ],
                    ],
                    'id',
                    'name',
                    'price_from',
                    'price_to',
                    'cost',
                    'sort',
                    [
                        'class' => BooleanColumn::className(),
                        'attribute' => 'active',
                    ],
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
