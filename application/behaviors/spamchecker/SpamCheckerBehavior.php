<?php

namespace app\behaviors\spamchecker;

use yii\base\Behavior;

class SpamCheckerBehavior extends Behavior
{
    private $data = [];
    private $checkers = [];

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function check()
    {
        $this->checkers = [];

        foreach ($this->data as $key => $value) {
            switch ($key) {
                case "yandex":
                    $this->checkers[] = new YandexSpamChecker($value);
                    break;
                case "akismet":
                    $this->checkers[] = new AkismetSpamChecker($value);
                    break;
            }
        }

        $results = [];
        foreach ($this->checkers as $checker) {
            $results[$checker->getType()] = $checker->check();
        }

        return $results;
    }
}
