<?php

namespace app\backgroundtasks\traits;

use app\modules\page\models\Page;
use app\models\SubscribeEmail;
use app\properties\HasProperties;
use Yii;

trait NewsletterSenderTrait
{
    public function sendEmail($id)
    {
        if (null === $id) {
            return;
        }

        $subscribeEmail = new SubscribeEmail();
        $pk = $subscribeEmail->primaryKey();
        $pk = $pk[0];
        if (null === $pk) {
            return;
        }

        $sEmail = $subscribeEmail->findOne([$pk => $id]);
        Yii::$app->mail->compose(
            '@app/views/notifications/newsletter/notify.php',
            [
                'name' => $sEmail->name
            ]
        )->setTo($sEmail->email)
            ->setFrom(Yii::$app->mail->transport->getUsername())
            ->setSubject(Yii::t('app', 'New info'))
            ->send();
    }

    public function sendToAllEmailsFromList()
    {
        $subscribeEmails = (new SubscribeEmail())->getActiveSubscribes();

        foreach ($subscribeEmails as $subscribeEmail) {
            $actualNews = $this->getActualNewsForSubsctibe($subscribeEmail);
            $sendStatus = false;
            if (count($actualNews) > 0) {
                $sendStatus = Yii::$app->mail->compose(
                    '@app/views/notifications/newsletter/notify.php',
                    [
                        'user' => $subscribeEmail->name,
                        'news' => $actualNews
                    ]
                )->setTo($subscribeEmail->email)
                    ->setFrom(Yii::$app->mail->transport->getUserName())
                    ->setSubject(Yii::t('app', 'Last news'))
                    ->send();
            }

            if ($sendStatus) {
                $nowFormat = date('Y-m-d H:i:s');
                $subscribeEmail->last_notify = $nowFormat;
                $subscribeEmail->save();
            }
        }
    }

    public function getActualNewsForSubsctibe($subscribe)
    {
        $actualNews = [];
        /** @var Page[]|HasProperties[] $pages */
        $pages = Page::find(
            [
                'is_deleted' => '0',
                'published' => '1'
            ]
        )->andWhere("date_added > '{$subscribe->last_notify}'")->all();
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                $prop = $page->getPropertyValuesByKey("mailingType");
                if ($prop == 'news') {
                    $n = [
                        'name' => $page->h1,
                        'announce' => $page->announce,
                        'date_added' => $page->date_added
                    ];
                    array_push($actualNews, $n);
                }
            }
        }

        return $actualNews;
    }
}
