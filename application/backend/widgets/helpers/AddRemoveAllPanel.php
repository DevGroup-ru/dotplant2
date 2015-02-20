<?php

namespace app\backend\widgets\helpers;

use Yii;
use \kartik\helpers\Html;
use kartik\icons\Icon;

/**
 * Standard panel for common backend grids footer with Add and RemoveAll buttons
 * @package app\backend\widgets\helpers
 */
class AddRemoveAllPanel extends \yii\base\Widget
{
    public $baseRoute = '/backend/dynamic-content/';

    /**
     * @inheritdoc
     */
    public function run()
    {
        return Html::a(
            Icon::show('plus') . Yii::t('app', 'Add'),
            [$this->baseRoute . 'edit'],
            ['class' => 'btn btn-success']
        ) . \app\backend\widgets\RemoveAllButton::widget([
            'url' => $this->baseRoute . 'remove-all',
            'gridSelector' => '.grid-view',
            'htmlOptions' => [
                'class' => 'btn btn-danger pull-right'
            ],
        ]);
    }
}