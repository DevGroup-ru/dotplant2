<?php

/**
 * @var \app\modules\user\models\User $user
 * @var \yii\web\View $this
 */

use yii\helpers\Url;

?>

<p>Welcome, <?= $user->first_name ?> <?= $user->last_name ?>!</p>
<ul>
    <li><strong>Your login:</strong> <?= $user->username ?></li>
    <li><strong>Your password:</strong> <?= $password; ?></li>
</ul>