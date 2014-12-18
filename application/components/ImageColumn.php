<?php

namespace app\components;

use app;
use kartik\icons\Icon;
use Yii;


class ImageColumn extends \yii\grid\DataColumn
{
    public $format = 'raw';

    public function getDataCellValue($model, $key, $index)
    {
        return
            "<img src=\"" . $model->getAttribute($this->attribute) . "\" class=\"image-column\">" .
            "<a href=\"#\" class=\"btn btn-xs btn-warning btn-change-image\"".
            " data-modelid=\"".$model->id."\" data-attribute=\"".$this->attribute."\"" .
            ">" . Icon::show('pencil') . "</a>";
    }
} 