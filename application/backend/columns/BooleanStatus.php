<?php

namespace app\backend\columns;

use Yii;
use yii\grid\DataColumn;

/**
 * Class BooleanStatus is column that renders label of boolean status(1/0)
 *
 * Example usage:
 * ```
 * [
 *     'class' => \app\backend\columns\BooleanStatus::className(),
 *     'attribute' => 'is_cacheable',
 *     'header' => Yii::t('app', 'Is cacheable'),
 * ],
 *
 * ```
 *
 * @package app\backend\columns
 */
class BooleanStatus extends DataColumn
{
    public $header = 'Status';
    
    public $true_value = 'Active';
    public $true_label_class = 'label-success';

    public $false_value = 'Inactive';
    public $false_label_class = 'label-default';


    protected function renderHeaderCellContent()
    {
        return trim($this->header) !== '' ? Yii::t('app', $this->header) : $this->grid->emptyCell;
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $content = parent::renderDataCellContent($model, $key, $index);
        if ($content == "1") {
            return "<span class=\"label ".$this->true_label_class."\">". Yii::t('app', $this->true_value) ."</span>";
        } else {
            return "<span class=\"label ".$this->false_label_class."\">".Yii::t('app', $this->false_value) ."</span>";
        }
    }
}
