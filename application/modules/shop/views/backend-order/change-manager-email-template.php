<?php

/**
 * @var \app\modules\user\models\User $manager
 * @var \app\modules\user\models\User $oldManager
 * @var \app\models\Order $order
 * @var \app\modules\user\models\User $user
 */

use yii\helpers\Html;

?>
<p>New order manager is <?= Html::encode($manager->getDisplayName()) ?>.</p>
<p>Initiator is <?= Html::encode($user->getDisplayName()) ?>.</p>
<p>Go to <a href="<?= \yii\helpers\Url::toRoute(['/backend/order/view', 'id' => $order->id], true) ?>">order</a>.</p>