<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;


$this->title = Yii::t('app', 'Currencies');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>
<?php $this->beginBlock('add-button'); ?>
<?= \yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['/backend/currencies/edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?= \app\backend\widgets\RemoveAllButton::widget([
    'url' => '/backend/currencies/remove-all',
    'gridSelector' => '.grid-view',
    'htmlOptions' => [
        'class' => 'btn btn-danger pull-right'
    ],
]); ?>
<?php $this->endBlock(); ?>



<div class="row">
    <div class="col-md-12">
        <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'currencies-grid',
                ],
                'columns' => [
                    [
                        'class' => \app\backend\columns\CheckboxColumn::className(),
                    ],
                    'id',
                    'name',
                    'iso_code',
                    'convert_nominal',
                    'convert_rate',
                    [
                        'attribute' => 'currency_rate_provider_id',
                        'class' => \kartik\grid\EditableColumn::className(),
                        'editableOptions' => [
                            'data' => [0=>'-']+\app\components\Helper::getModelMap(\app\models\CurrencyRateProvider::className(), 'id', 'name'),
                            'inputType' => 'dropDownList',
                            'placement' => 'left',
                            'formOptions' => [
                                'action' => 'update-editable',
                            ],
                        ],
                        'filter' => \app\components\Helper::getModelMap(\app\models\CurrencyRateProvider::className(), 'id', 'name'),
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            if ($model === null || $model->rateProvider === null) {
                                return null;
                            }
                            return Html::tag('div', $model->rateProvider->name, ['class' => $model->rateProvider->name]);
                        },
                    ],
                    [
                        'class' => app\backend\columns\ActionColumn::className(),
                    ],
                ],
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover' => true,
                    'panel' => [
                        'heading' => $this->render('@app/backend/views/currencies/_tabs', [
                            'currencies' => true
                        ]),
                        'after' => $this->blocks['add-button'],
                    ],
                ]
            ]
        );
        ?>
    </div>
</div>
