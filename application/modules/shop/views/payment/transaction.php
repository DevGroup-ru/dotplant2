<?php
/**
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
use \app\modules\shop\models\OrderTransaction;
use \yii\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;

    $paymentType = $transaction->paymentType;
?>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th><?= Html::encode($transaction->getAttributeLabel('id'))?></th>
        <th><?= Html::encode($transaction->getAttributeLabel('payment_type_id'))?></th>
        <th><?= Html::encode($transaction->getAttributeLabel('total_sum'))?></th>
        <th><?= Html::encode($transaction->getAttributeLabel('status'))?></th>
        <th><?= Html::encode($transaction->getAttributeLabel('start_date'))?></th>
        <th><?= Html::encode($transaction->getAttributeLabel('end_date'))?></th>
    </tr>
    </thead>
    <tbody>
    <?php
        echo '<tr>';
        echo Html::tag('td', $transaction->id);
        echo Html::tag('td', $transaction->paymentType->name);
        echo Html::tag('td', $transaction->total_sum);
        echo Html::tag('td', $transaction->getTransactionStatus());
        echo Html::tag('td', $transaction->start_date);
        echo Html::tag('td', $transaction->end_date);
        echo '</tr>';
    ?>
    </tbody>
</table>

<?= OrderTransaction::TRANSACTION_START === $transaction->status
    ? $paymentType->getPayment($transaction->order, $transaction)->content()
    : '';
?>
