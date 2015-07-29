<?php

namespace app\modules\image\widgets\views;


use app\modules\image\models\Image;
use yii\base\Action;

class AddImageAction extends Action
{
    public function run()
    {
        $filename = \Yii::$app->request->get('filename', '');
        $filename = trim($filename, '/');
        $objId = \Yii::$app->request->get('objId', '');
        $objModelId = \Yii::$app->request->get('objModelId', '');
        if (empty($filename) === false) {
            $image = new Image;
            $image->loadDefaultValues();
            $image->setAttributes(['filename' => $filename, 'object_id' => $objId, 'object_model_id' => $objModelId]);
            $image->save();
        }
    }
}