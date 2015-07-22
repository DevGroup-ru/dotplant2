<?php

namespace app\modules\image\widgets;

use app\components\Helper;
use Yii;
use app\modules\image\models\Image;
use yii\helpers\ArrayHelper;
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
        if (isset($data['params']) === false) {
            return ['error' => 'bad request'];
        }
        if (isset($data['params']['objectId'], $data['filename'], $data['params']['modelId']) === false) {
            return ['error' => 'bad request'];
        }
        if ($data['params']['modelId'] === 'null') {
            $data['params']['modelId']=0;
        }
        $image = new Image(
            [
                'object_id' => $data['params']['objectId'],
                'object_model_id' => $data['params']['modelId'],
                'filename' => $data['filename'],
                'image_title' => '',
                'sort_order' => 0,
            ]
        );
        if ($image->save()) {
            return ArrayHelper::merge($image->toArray(), ['file'=>$image->file]);
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

        $transliteratedFileName = Helper::createSlug(
            substr(strstr(strtolower($file->name), $file->extension, true), 0, - 1)
        );
        $fileName = $transliteratedFileName . '.' . $file->extension;
        if (Yii::$app->getModule('image')->fsComponent->has($fileName)) {
            $fileName = $transliteratedFileName . '-' . uniqid() . '.' . $file->extension;
        }
        Yii::$app->getModule('image')->fsComponent->put($fileName, file_get_contents($file->tempName));
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
