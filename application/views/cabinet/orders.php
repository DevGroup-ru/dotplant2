<?php

/**
 * @var $canceledOrders \app\models\Order[]
 * @var $currentOrders \app\models\Order[]
 * @var $doneOrders \app\models\Order[]
 * @var $this yii\web\View
 */

$this->title = Yii::t('app', 'Your orders');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' => '/cabinet'
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
                    'label' => Yii::t('app', 'Current ({n})', ['n' => count($currentOrders)]),
                ],
                [
                    'content' => $this->render('orders-table', ['orders' => $doneOrders, 'showEndDate' => true]),
                    'label' => Yii::t('app', 'Done ({n})', ['n' => count($doneOrders)]),
                ],
                [
                    'content' => $this->render('orders-table', ['orders' => $canceledOrders, 'showEndDate' => true]),
                    'label' => Yii::t('app', 'Canceled ({n})', ['n' => count($canceledOrders)]),
                ],
            ],
        ]
    );
