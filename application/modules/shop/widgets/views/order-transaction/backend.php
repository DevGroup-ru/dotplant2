<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $model
 * @var boolean $immutable
 * @var array $additional
 */

    $transactionsDataProvider = $additional['transactionsDataProvider'];
//var_dump($model);
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
                        ));
                    },
                    'format' => 'raw',
                    'encodeLabel' => false,
                    'label' => Yii::t('app', 'ID')
                ],

                [
                    'attribute' => 'status',
                    'value' => function ($model, $key, $index, $column) {
                        /** @var \app\modules\shop\models\OrderTransaction $model */
                        return $model->getTransactionStatus();
                    },
                    'label' => Yii::t('app', 'Status')
                ],

                [
                    'attribute' => 'start_date',
                    'label' => Yii::t('app', 'Start Date')
                ],

                [
                    'attribute' => 'end_date',
                    'label' => Yii::t('app', 'End Date')
                ],

                [
                    'attribute' => 'total_sum',
                    'label' => Yii::t('app', 'Total Sum')
                ],

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
                        return Yii::t('app', $model->paymentType->name);
                    },
                    'label' => Yii::t('app', 'Payment Type')
                ],

            ],
        ]
    );
?>