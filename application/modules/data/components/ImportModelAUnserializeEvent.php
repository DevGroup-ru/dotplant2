<?php

namespace app\modules\data\components;

use Yii;
use yii\base\Event;

class ImportModelAUnserializeEvent extends Event
{
    public $fields = [];
}