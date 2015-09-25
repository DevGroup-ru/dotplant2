<?php
namespace app\modules\user\models;

use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => User::className(),
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t('app', 'There is no user with such email.'),
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);
        if ($user) {
            if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }
            $user->scenario = 'passwordResetToken';
            if ($user->save(true, ['password_reset_token'])) {
                return Yii::$app->mail->compose('password-reset-token', ['user' => $user])
                    ->setFrom(Yii::$app->getModule('core')->emailConfig['mailFrom'])
                    ->setTo($this->email)
                    ->setSubject(
                        Yii::t(
                            'app',
                            'Password reset for {appName}',
                            [
                                'appName' => Yii::$app->getModule('DefaultTheme')->siteName
                            ]
                        )
                    )
                    ->send();
            }
        }
        return false;
    }
}
