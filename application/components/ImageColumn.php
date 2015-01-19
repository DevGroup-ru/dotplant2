<?php

namespace app\components;

use app;
use kartik\icons\Icon;
use Yii;
use yii\helpers\Html;


class ImageColumn extends \yii\grid\DataColumn
{
    public $format = 'raw';

    public function getDataCellValue($model, $key, $index)
    {
        $img = Html::img($model->getAttribute($this->attribute), ['class' => 'image-column']);
        $editLink = Html::a(Icon::show('pencil'), '#', [
            'class' => 'btn btn-xs btn-warning btn-change-image',
            'data-modelid' => $model->id,
            'data-attribute' => $this->attribute
        ]);
        return $img . $editLink;
    }
} 