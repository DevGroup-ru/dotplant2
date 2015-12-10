<?php

namespace app\components\search;


use yii\base\Event;
use yii\helpers\ArrayHelper;

class SearchEvent extends Event
{

    public $activeQuery = null;
    public $q = null;
    public $functionSearch = null;

    public function init()
    {
        if ($this->functionSearch === null) {
            $this->functionSearch = function ($activeQuery) {
                return ArrayHelper::getColumn($activeQuery->all(), 'id');
            };
        }
        parent::init();
    }

    public function getAll()
    {
        $result = [];
        if (is_callable($this->functionSearch)) {
            $method = $this->functionSearch;
            $result = $method($this->activeQuery);
        }
        return $result;
    }

}