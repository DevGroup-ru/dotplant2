<?php

namespace app\modules\core\components;

use app\modules\core\CoreModule;
use yii\swiftmailer\Mailer;

class MailComponent extends Mailer
{
    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $mailConfig = \Yii::$app->getModule('core')->emailConfig;
        $_config = ['class' => $mailConfig['transport']];
        if ('Swift_SmtpTransport' === $mailConfig['transport']) {
            $_config['host'] = $mailConfig['host'];
            $_config['username'] = $mailConfig['username'];
            $_config['password'] = $mailConfig['password'];
            $_config['port'] = $mailConfig['port'];
            $_config['encryption'] = $mailConfig['encryption'];
        } elseif ('Swift_SendmailTransport' === $mailConfig['transport']) {
            $_config['command'] = $mailConfig['sendMail'];
        }
        $this->setTransport($_config);
    }
}