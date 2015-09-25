<?php

/**
 * @var \app\modules\user\models\User $user
 * @var \yii\web\View $this
 */

use yii\helpers\Url;

?>

<p><?= Yii::t('app', 'Welcome, {name}!', ['name' => $user->first_name . ' ' . $user->last_name]) ?></p>
<ul>
    <li><?= Yii::t('app', '<strong>Your login:</strong> {username}', ['username' => $user->username]); ?></li>
    <li><?= Yii::t('app', '<strong>Your password:</strong> {password}', ['password' => $password]); ?></li>
</ul>