<?php

/**
 * @var \app\modules\user\models\User $user
 * @var \yii\web\View $this
 */

use yii\helpers\Url;

?>

<p><?= Yii::t('app', 'Hello'); ?>, <?= $user->username ?>!</p>
<p>
    <?= Yii::t(
        'app',
        '<a href="{link}">This</a> is link for reset your password. If you did not request it ignore this letter.',
        [
            'link' => Url::toRoute(
                [
                    '/user/user/reset-password',
                    'token' => $user->password_reset_token
                ],
                true
            )
        ]
    ); ?>
</p>
