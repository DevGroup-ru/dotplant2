<?php

namespace app\components\search;


use yii\base\Event;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class SearchEvent extends Event
{

    /**
     * @var ActiveQuery
     */
    public $activeQuery = null;
    /**
     * @var string
     */
    public $q = null;
    /**
     * @var callable
     */
    protected $functionSearch = null;

    /**
     * @return callable
     */
    public function getFunctionSearch()
    {
        return $this->functionSearch;
    }

    /**
     * @param callable $functionSearch
     */
    public function setFunctionSearch(callable $functionSearch)
    {
        $this->functionSearch = $functionSearch;
    }

    public function init()
    {
        if ($this->functionSearch === null) {
            $this->setFunctionSearch(function ($activeQuery) {
                return ArrayHelper::getColumn($activeQuery->all(), 'id');
            });
        }
        parent::init();
    }

    /**
     * @return array
     */
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