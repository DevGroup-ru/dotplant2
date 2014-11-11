<?php

namespace app\backend\columns;

use yii\grid\DataColumn;

class TextWrapper extends DataColumn
{
    public $callback_wrapper = null;

    protected function renderDataCellContent($model, $key, $index)
    {
        $content = parent::renderDataCellContent($model, $key, $index);

        if (null === $this->callback_wrapper) {
            return $content;
        }

        if ($this->callback_wrapper instanceof \Closure) {
            $_content = call_user_func($this->callback_wrapper, $content, $model, $key, $index, $this);
            if (null !== $_content) {
                return $_content;
            }
        }

        return $content;
    }
}
