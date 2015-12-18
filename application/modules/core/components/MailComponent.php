<?php

namespace app\modules\core\components;

use app\commands\SubmissionsController;
use app\modules\core\components\mail\SendMailHandler;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\swiftmailer\Mailer;

class MailComponent extends Mailer implements BootstrapInterface
{
    private $componentConfig = [];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application === true) {
            Event::on(
                SubmissionsController::className(),
                SubmissionsController::EVENT_SEND_SUBMISSIONS,
                [
                    SendMailHandler::class,
                    'submissionsSendMail'
                ]
            );
        }
    }


    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->componentConfig = $mailConfig = \Yii::$app->getModule('core')->emailConfig;

        $_config = [
            'class' => $mailConfig['transport'],
        ];

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

    /**
     * @return string
     */
    public function getMailFrom()
    {
        return !empty($this->componentConfig['mailFrom'])
            ? $this->componentConfig['mailFrom']
            : '';
    }
}