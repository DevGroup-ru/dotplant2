<?php

namespace app\widgets;

use app\models\Image;
use Yii;
use yii\base\Widget;

class ImgSearch extends Widget
{
    public $objectId = null;
    public $objectModelId = null;
    public $viewFile = 'img';
    public $limit = null;
    public $offset = 0;

    public function run()
    {
        $images = Image::getForModel($this->objectId, $this->objectModelId);
        if ($this->offset > 0 || !is_null($this->limit)) {
            $images = array_slice($images, $this->offset, $this->limit);
        }
        return $this->render(
            $this->viewFile,
            ['images' => $images]
        );
    }
}
