<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $model
 * @var boolean $immutable
 * @var array $additional
 */
use \yii\helpers\Url;
use \yii\helpers\Html;
use \kartik\icons\Icon;

    $dummy = new \app\modules\shop\models\OrderTransaction();
?>

    <h3>Транзакции</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?= Html::encode($dummy->getAttributeLabel('id'))?></th>
                <th><?= Html::encode($dummy->getAttributeLabel('payment_type_id'))?></th>
                <th><?= Html::encode($dummy->getAttributeLabel('total_sum'))?></th>
                <th><?= Html::encode($dummy->getAttributeLabel('status'))?></th>
                <th><?= Html::encode($dummy->getAttributeLabel('start_date'))?></th>
                <th><?= Html::encode($dummy->getAttributeLabel('end_date'))?></th>
            </tr>
        </thead>
        <tbody>
    <?php
        foreach ($model->transactions as $transaction) {
            echo '<tr>';
//            if ($immutable) {
                echo Html::tag('td', $transaction->id);
//            } else {
//                echo Html::tag('td',
//                    Html::a(
//                        $transaction->id,
//                        Url::toRoute([
//                            '/shop/payment/transaction',
//                            'id' => $transaction->id,
//                            'othash' => $transaction->generateHash()
//                        ])
//                    ));
//            }
            echo Html::tag('td', $transaction->paymentType->name);
            echo Html::tag('td', $transaction->total_sum);
            echo Html::tag('td', $transaction->getTransactionStatus());
            echo Html::tag('td', $transaction->start_date);
            echo Html::tag('td', $transaction->end_date);
            echo '</tr>';
        }
    ?>
        </tbody>
    </table>
