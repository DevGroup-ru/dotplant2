<?php

namespace app\behaviors\spamchecker;

use Yii;
use yii\helpers\ArrayHelper;

class AkismetSpamChecker implements SpamCheckable
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getType()
    {
        return 'akismet';
    }

    public function check()
    {
        $serviceUrl = 'http://' . $this->data['key'] . '.rest.akismet.com/1.1/comment-check';
        $query = http_build_query(
            ArrayHelper::merge(
                $this->data,
                [
                    'blog' => Yii::$app->request->hostInfo,
                    'user_ip' => Yii::$app->request->userIP,
                    'user_agent' => Yii::$app->request->userAgent,
                    'referrer' => Yii::$app->request->referrer,
                ]
            )
        );
        $curl = curl_init();
        if ($curl === false) {
            return [
                'ok' => "0",
                'message' => 'curl_init failed'
            ];
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $serviceUrl);
        $response = curl_exec($curl);
        if ($response === false) {
            return [
                'ok' => false,
                'message' => curl_error($curl)
            ];
        }
        return [
            'ok' => true,
            'is_spam' => $response == 'true',
        ];
    }
}
