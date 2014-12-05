<?php

namespace app\index;

use app;
use Yii;
use yii\base\Component;

class IndexComponent extends Component
{
    public $storageComponent = 'elasticsearch';

    public $delayedIndexation = true;

    public function storage()
    {
        $componentName = $this->storageComponent;
        return Yii::$app->$componentName;
    }
} 