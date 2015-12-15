<?php
namespace app\modules\shop\events;

use yii\base\Event;

class UserPreferenceEvent extends Event
{
    private $_key = null;
    private $_value = null;

    /**
     * @inheritdoc
     */
    public function __construct($key, $value, $config = [])
    {
        parent::__construct($config);

        $this->_key = $key;
        $this->_value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }
}
