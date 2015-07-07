<?php

namespace app\behaviors\spamchecker;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class SpamCheckerBehavior extends Behavior
{
    /**
     * @var array
     */
    private $data = [];

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

    /**
     * Check data
     * @param bool $asArray
     * @return array|bool
     */
    public function isSpam($asArray = false)
    {
        $results = [];
        foreach ($this->data as $data) {
            $class = ArrayHelper::getValue($data, 'class', '');
            $value = ArrayHelper::getValue($data, 'value', []);
            /** @var SpamCheckable $checker */
            $checker = new $class($value);
            $result = $checker->check();
            if ($asArray) {
                $results[$checker->getType()] = $result;
            } else {
                if (ArrayHelper::getValue($result, 'ok', false) && ArrayHelper::getValue($result, 'is_spam', false)) {
                    return true;
                }
            }
        }
        return $asArray ? $results : false;
    }
}
