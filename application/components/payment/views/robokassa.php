<?php
/**
 * @var string $url
 * @var \app\models\Order $order
 * @var \app\models\OrderTransaction $transaction
 */
?>
<a href='<?= $url ?>'><?= Yii::t('shop', 'Pay') ?></a>
<script>window.location='<?= $url ?>';</script>