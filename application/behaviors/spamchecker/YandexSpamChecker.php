<?php

namespace app\behaviors\spamchecker;

use Yii;

class YandexSpamChecker implements SpamCheckable
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getType()
    {
        return 'yandex';
    }

    /**
     * @return array
     */
    public function check()
    {
        $this->data['ip'] = Yii::$app->request->userIP;
        $query = http_build_query($this->data);
        $curl = curl_init();
        if ($curl === false) {
            return [
                'ok' => false,
                'message' => 'curl_init failed'
            ];
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, "http://cleanweb-api.yandex.ru/1.0/check-spam");
        $response = curl_exec($curl);
        curl_close($curl);
        if ($response === false) {
            return [
                'ok' => false,
                'message' => curl_error($curl)
            ];
        }
        try {
            $xmlObj = new \SimpleXMLElement($response);
            if (isset($xmlObj->id, $xmlObj->text)) {
                $id = (string) $xmlObj->id;
                $attributes = $xmlObj->text->attributes();
                $isSpam = isset($attributes['spam-flag']) ? $attributes['spam-flag'] == 'yes' : false;
            } elseif (isset($xmlObj->message)) {
                return [
                    'ok' => false,
                    'message' => (string) $xmlObj->message
                ];
            } else {
                return [
                    'ok' => false,
                    'message' => 'invalid response'
                ];
            }
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Exception: ' . $e->getMessage() . ' with code: ' . $e->getCode()
            ];
        }
        return [
            'ok' => true,
            'responce_id' => $id,
            'is_spam' => $isSpam,
        ];
    }
}
