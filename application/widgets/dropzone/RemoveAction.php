<?php

namespace app\widgets\dropzone;

use Yii;
use yii\base\Action;

class RemoveAction extends Action
{
    public $uploadDir = '@webroot/upload';

    public function run($fileName)
    {
        return (int)unlink(Yii::getAlias($this->uploadDir) . '/' . $fileName);
    }
}
