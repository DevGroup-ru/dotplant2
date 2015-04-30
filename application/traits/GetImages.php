<?php

namespace app\traits;

use app\modules\image\models\Image;

/**
 * Trait to create relation of images width object
 * Class GetImages
 * @package app\traits
 */
trait GetImages
{
    public function getImages()
    {
        /**
         * @var $object \app\models\Object
         * @var $model \app\properties\HasProperties | \yii\db\ActiveRecord
         * @return \yii\db\ActiveQueryInterface
         */
        $model = $this;
        $object = $model->object;
        return $model->hasMany(Image::className(), ['object_model_id' => 'id'])->andWhere(
            ['object_id' => $object->id]
        )->addOrderBy(
            [
                'sort_order' => SORT_ASC,
                'id' => SORT_ASC
            ]
        );
    }

}
