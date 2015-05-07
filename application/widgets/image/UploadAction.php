<?php

namespace app\widgets\image;

use Yii;
use app\modules\image\models\Image;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\UploadedFile;

class UploadAction extends \devgroup\dropzone\UploadAction
{
    public $thumbnail_width = 80;
    public $thumbnail_height = 80;


    public function init()
    {
        parent::init();
        if (!isset($this->afterUploadHandler)) {
            $this->afterUploadHandler = [$this, 'afterUpload'];
        }
    }

    public function afterUpload($data)
    {
        $image = new Image(
            [
                'object_id' => $data['params']['objectId'],
                'object_model_id' => $data['params']['modelId'],
                'filename' => $data['filename'],
                'image_description' => '',
                'sort_order' => 0,
            ]
        );
        if ($image->save()) {
            return $image->toArray();
        } else {
            return $image->getErrors();
        }

    }

    public function run()
    {
        $file = UploadedFile::getInstanceByName($this->fileName);
        if ($file->hasError) {
            throw new HttpException(500, 'Upload error');
        }

        $fileName = $file->name;
        if (Yii::$app->fs->has($fileName)) {
            $fileName = $file->baseName . '-' . uniqid() . '.' . $file->extension;
        }

        $stream = fopen($file->tempName, 'r+');
        Yii::$app->fs->writeStream($fileName, $stream);
        fclose($stream);
        $response = [
            'filename' => $fileName,
        ];

        if (isset($this->afterUploadHandler)) {
            $data = [
                'data' => $this->afterUploadData,
                'file' => $file,
                'dirName' => $this->uploadDir,
                'filename' => $fileName,
                'params' => Yii::$app->request->post(),
            ];

            if ($result = call_user_func($this->afterUploadHandler, $data)) {
                $response['afterUpload'] = $result;
            }
        }

        return Json::encode($response);
    }
}
