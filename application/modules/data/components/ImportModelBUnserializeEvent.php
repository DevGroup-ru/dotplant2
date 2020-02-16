<?php

namespace app\modules\data\components;

use Yii;
use yii\base\Event;

class ImportModelBUnserializeEvent extends Event
{
    public $serialized = '';
}