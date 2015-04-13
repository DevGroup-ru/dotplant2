<?php
/**
 * @var string $url
 * @var \app\models\Order $order
 * @var \app\models\OrderTransaction $transaction
 */

use yii\helpers\Html;

?>
<?= Html::a(Yii::t('app', 'Go To Payment'), $url) ?>
<script>window.location = '<?= $url ?>';</script>