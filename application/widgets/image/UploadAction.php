<?php

namespace app\widgets\image;


use app\models\Image;
use Yii;

class UploadAction extends \app\widgets\dropzone\UploadAction
{
    public function init()
    {
        parent::init();
        if (!isset($this->afterUploadHandler)) {
            $this->afterUploadHandler = [$this, 'afterUpload'];
        }
    }

    public function afterUpload($data)
    {
        ImageDropzone::saveThumbnail($this->uploadDir . '/', $data['filename']);
        $image = new Image([
            'object_id' => $data['params']['objectId'],
            'object_model_id' => $data['params']['modelId'],
            'filename' => $data['filename'],
            'image_src' => $data['src'] . $data['filename'],
            'thumbnail_src' => $data['src'] . 'small-' . $data['filename'],
            'image_description' => '',
            'sort_order' => 0,
        ]);
        if ($image->save()) {
            return $image->toArray();
        } else {
            return $image->getErrors();
        }
    }
}
