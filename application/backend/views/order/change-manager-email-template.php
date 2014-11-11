<?php

/**
 * @var \app\models\User $manager
 * @var \app\models\User $oldManager
 * @var \app\models\Order $order
 * @var \app\models\User $user
 */

use yii\helpers\Html;

?>
<p>New order manager is <?= Html::encode($manager->getAwesomeUsername()) ?>.</p>
<p>Initiator is <?= Html::encode($user->getAwesomeUsername()) ?>.</p>
<p>Go to <a href="<?= \yii\helpers\Url::toRoute(['/backend/order/view', 'id' => $order->id], true) ?>">order</a>.</p>