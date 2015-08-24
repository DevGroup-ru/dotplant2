<?php

/**
 * @var \app\modules\user\models\User $user
 * @var \yii\web\View $this
 */

use yii\helpers\Url;

?>

<p>Hello, <?= $user->username ?>!</p>
<p><a href="<?= Url::toRoute(['/user/user/reset-password', 'token' => $user->password_reset_token], true) ?>">This</a> is link for reset your password. If you did not request it ignore this letter.</p>
