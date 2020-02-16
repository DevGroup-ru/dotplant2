<?php

namespace app\modules\data\components;

use Yii;
use yii\base\Event;

class ImportModelLoadEvent extends Event
{
    public $_data = [];
    public $_formName = null;
}