<?php
use yii\helpers\Html;
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order[] $orders
 * @var string $currentOrder
 * @var \yii\data\ArrayDataProvider $dataProvider
 */
$this->title = Yii::t('app', 'Orders list');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' => '/shop/cabinet'
    ],
    $this->title,
];

//
//    echo \yii\helpers\Html::tag('p', 'Current order: '.$currentOrder);
//    foreach ($orders as $order) {
//        echo \yii\helpers\Html::tag(
//            'p',
//            \yii\helpers\Html::a(
//                'Order: '.$order->id,
//                \yii\helpers\Url::to(['/shop/orders/show', 'hash' => $order->hash])
//            )
//        );
//    }
?>
<h1>
    <?= Yii::t('app', 'Orders list') ?>
</h1>

<?php if ($currentOrder): ?>
    <div class="current-order">
        <?= Yii::t('app', 'Current order:') ?>
        <?= Html::a($currentOrder, ['/shop/cart']) ?>
    </div>
<?php endif; ?>
<?php if ($dataProvider !== null):?>
    <?=
        \yii\grid\GridView::widget([
            'columns' => [
                [
                    'attribute' => 'id',
                    'value' => function($model, $key, $index, $column) {
                        return Html::a('#'.$model->id, ['/shop/orders/show', 'hash' => $model->hash]);
                    },
                    'format' => 'raw',
                ],
                'start_date:date',
                [
                    'attribute' => 'stage.name_frontend',
                    'header' => Yii::t('app', 'Status'),
                ],
                [
                    'attribute' => 'total_price',
                    'header' => Yii::t('app', 'Total price'),
                    'value' => function($model, $key, $index, $column) {
                        return \app\modules\shop\models\Currency::getMainCurrency()->format($model->total_price);
                    },
                    'format' => 'raw',
                ],
            ],
            'dataProvider' => $dataProvider,
        ]);
    ?>

<?php else: ?>
    <?= Yii::t('app', 'You have no complete orders') ?>

<?php endif; ?>
