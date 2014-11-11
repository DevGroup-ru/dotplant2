<?php

namespace app\commands;

use app\backend\models\NewsletterConfig;
use app\backgroundtasks\traits\NewsletterSenderTrait;
use yii\console\Controller;

class NewsletterController extends Controller
{
    use NewsletterSenderTrait;

    public function actionNotifyAll()
    {
        $newsletterConfig = new NewsletterConfig();
        if ($newsletterConfig->isActive) {
            $this->sendToAllEmailsFromList();
        }
    }
}
