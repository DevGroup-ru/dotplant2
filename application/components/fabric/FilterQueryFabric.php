<?php

namespace app\components\fabric;

use Yii;
use yii\base\Component;

class FilterQueryFabric extends Component
{
    public $filter = null;

    public function init()
    {
        parent::init();
        if (null === $this->filter) {
            $this->filter = 'app\components\fabric\DummyFilterQuery';
        }
        if (!class_exists($this->filter)) {
            $this->filter = null;
        }
    }

    public function getFilter()
    {
        if (null !== $this->filter) {
            $filter = Yii::createObject($this->filter);
            if ($filter instanceof FilterQueryInterface) {
                return $filter;
            }
        }
        return null;
    }
}
