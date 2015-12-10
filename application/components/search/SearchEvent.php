<?php

namespace app\components\search;


use yii\base\Event;

class SearchEvent extends Event
{

    public $activeQuery = null;
    public $q = null;

}