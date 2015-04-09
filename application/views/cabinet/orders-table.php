<?php

/**
 * @var $orders \app\models\Order[]
 * @var $showEndDate boolean
 * @var $this yii\web\View
 */

$showEndDate = isset($showEndDate) && $showEndDate;

?>
<?php if (count($orders) > 0): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?= $orders[0]->getAttributeLabel('start_date') ?></th>
                <?php if ($showEndDate): ?>
                    <th><?= $orders[0]->getAttributeLabel('end_date') ?></th>
                <?php endif; ?>
                <th><?= $orders[0]->getAttributeLabel('order_status_id') ?></th>
                <th><?= $orders[0]->getAttributeLabel('items_count') ?></th>
                <th><?= $orders[0]->getAttributeLabel('total_price') ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
                <tr>
                    <td><?= $order->start_date ?></td>
                    <?php if ($showEndDate): ?>
                        <td><?= $order->end_date ?></td>
                    <?php endif; ?>
                    <td>
                        <?=
                            isset($order->status)
                                ? \yii\helpers\Html::tag(
                                    'span',
                                    $order->status->short_title,
                                    [
                                        'class' => $order->status->label,
                                    ]
                                )
                                : Yii::t('yii', '(not set)')
                        ?>
                    </td>
                    <td><?= $order->items_count ?></td>
                    <td><?= Yii::$app->formatter->asDecimal($order->total_price, 2) ?></td>
                    <td><?= \yii\helpers\Html::a('See order', ['/cabinet/order', 'id' => $order->hash]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p><?= Yii::t('app', 'Empty') ?></p>
<?php endif; ?>