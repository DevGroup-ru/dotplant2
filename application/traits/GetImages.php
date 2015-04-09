<?php

namespace app\traits;

use app\models\Image;

/**
 * Trait to create relation of images width object
 * Class GetImages
 * @package app\traits
 * @var $this \yii\db\ActiveRecord
 */
trait GetImages
{
    public function getImages()
    {
        /**
         * @var $object \app\models\Object
         */
        $object = $this->object;
        return $this->hasMany(Image::className(), ['object_model_id' => 'id'])->onCondition(
            ['object_id' => $object->id]
        );
    }
}