<?php

/**
 * @var $canceledOrders \app\models\Order[]
 * @var $currentOrders \app\models\Order[]
 * @var $doneOrders \app\models\Order[]
 * @var $this yii\web\View
 */

$this->title = Yii::t('shop', 'Your orders');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' => Yii::$app->request->baseUrl.'/cabinet'
    ],
    $this->title,
];

?>
<h1><?= $this->title ?></h1>

<?=
    \yii\bootstrap\Tabs::widget(
        [
            'items' => [
                [
                    'active' => true,
                    'content' => $this->render('orders-table', ['orders' => $currentOrders]),
                    'label' => Yii::t('shop', 'Current ({n})', ['n' => count($currentOrders)]),
                ],
                [
                    'content' => $this->render('orders-table', ['orders' => $doneOrders, 'showEndDate' => true]),
                    'label' => Yii::t('shop', 'Done ({n})', ['n' => count($doneOrders)]),
                ],
                [
                    'content' => $this->render('orders-table', ['orders' => $canceledOrders, 'showEndDate' => true]),
                    'label' => Yii::t('shop', 'Canceled ({n})', ['n' => count($canceledOrders)]),
                ],
            ],
        ]
    );
