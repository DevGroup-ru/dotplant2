<?php

namespace app\behaviors\spamchecker;

class AkismetSpamChecker implements SpamCheckable
{
    private $data = [];

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
        $serviceUrl = "http://" . $this->data['key'] . ".rest.akismet.com/1.1/submit-spam";

        if (!isset($this->data['blog']) && isset($_SERVER['HTTP_HOST'])) {
            $this->data['blog'] = $_SERVER['HTTP_HOST'];
        }
        if (!isset($this->data['user_ip']) && isset($_SERVER['REMOTE_ADDR'])) {
            $this->data['user_ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if (!isset($this->data['user_agent']) && isset($_SERVER['USER_AGENT'])) {
            $this->data['user_agent'] = $_SERVER['USER_AGENT'];
        }
        if (!isset($this->data['referrer']) && isset($_SERVER['HTTP_REFERER'])) {
            $this->data['referrer'] = $_SERVER['HTTP_REFERER'];
        }

        $query = http_build_query($this->data);

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
            'is_spam' => $response == "Thanks for making the web a better place." ? true : false
        ];
    }
}
