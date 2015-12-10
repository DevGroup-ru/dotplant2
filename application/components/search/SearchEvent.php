<?php

namespace app\components\search;


use yii\base\Event;
use yii\helpers\ArrayHelper;

class SearchEvent extends Event
{

    public $activeQuery = null;
    public $q = null;
    public $functionSearch;

    public function init()
    {
        $this->functionSearch = function($activeQuery){
          return ArrayHelper::getColumn($activeQuery->all(), 'id');
        };

        parent::init();
    }
    public function getAll()
    {
        $method = $this->functionSearch;
        return $method($this->activeQuery);
    }

}