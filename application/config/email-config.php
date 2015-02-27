<?php
return [
    'class' => 'yii\\swiftmailer\\Mailer',
    'transport' => [
        'host' => '',
        'port' => '587',
        'encryption' => 'tls',
        'username' => '',
        'password' => '',
        'class' => 'Swift_SmtpTransport',
    ],
];
