<?php

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Measures');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="order-status-index">
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
                            ['edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                            ['class' => 'btn btn-success']
                        ) . \app\backend\widgets\RemoveAllButton::widget([
                            'url' => '/backend/order-status/remove-all',
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
                'symbol',
                'nominal',
                [
                    'class' => ActionColumn::className(),
                ],
            ],
        ]
    );
    ?>
</div>
