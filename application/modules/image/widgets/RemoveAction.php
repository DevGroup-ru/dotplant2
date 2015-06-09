<?php

namespace app\modules\image\widgets;

use app\modules\image\models\Image;
use yii\base\Action;

class RemoveAction extends Action
{
    public $uploadDir = '@webroot/upload';

    public function run($id, $filename)
    {
        $image = Image::findOne($id);
        return $image->delete();
    }
}
