<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $model
 * @var boolean $immutable
 * @var array $additional
 */

    $transactionsDataProvider = $additional['transactionsDataProvider'];
?>
<?=
    \kartik\dynagrid\DynaGrid::widget(
        [
            'options' => [
                'id' => 'transactions-grid',
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $transactionsDataProvider,
                'hover' => true,
                'panel' => false
            ],
            'columns' => [
                [
                    'attribute' => 'id',
                    'value' => function ($model, $key, $index, $column) {
                        /** @var \app\modules\shop\models\OrderTransaction $model */
                        return \yii\helpers\Html::a($model->id, \yii\helpers\Url::toRoute(
                            ['/shop/payment/transaction', 'id' => $model->id, 'othash' => $model->generateHash()]
                            ),
                            ['class' => 'print-without-link']
                        );
                    },
                    'format' => 'raw',
                    'encodeLabel' => false,
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model, $key, $index, $column) {
                        /** @var \app\modules\shop\models\OrderTransaction $model */
                        return $model->getTransactionStatus();
                    },
                ],
                'start_date',
                'end_date',
                'total_sum',
                [
                    'attribute' => 'payment_type_id',
                    'filter' => \app\components\Helper::getModelMap(
                        \app\modules\shop\models\PaymentType::className(),
                        'id',
                        'name'
                    ),
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->paymentType === null) {
                            return null;
                        }
                        return $model->paymentType->name;
                    },
                ],

            ],
        ]
    );
?>