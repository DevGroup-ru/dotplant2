<?php

namespace app\behaviors\spamchecker;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;

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

        foreach ($this->data as $data) {
            $class = ArrayHelper::getValue($data, 'class', '');
            $value = ArrayHelper::getValue($data, 'value', []);
            $this->checkers[] = new $class($value);
        }

        $results = [];
        foreach ($this->checkers as $checker) {
            $results[$checker->getType()] = $checker->check();
        }

        return $results;
    }
}
