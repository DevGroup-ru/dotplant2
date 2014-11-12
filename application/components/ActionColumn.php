<?php

namespace app\components;

use kartik\icons\Icon;
use Yii;
use yii\helpers\Html;

class ActionColumn extends \yii\grid\ActionColumn
{
    /**
    * Initializes the default button rendering callbacks
    */
    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model) {
                return Html::a(
                    Icon::show('eye', ['class' => 'fa-lg'], Icon::FA),
                    $url,
                    [
                        'title' => \Yii::t('yii', 'View'),
                        'data-pjax' => '0',
                    ]
                );
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model) {
                return Html::a(
                    Icon::show('edit', ['class' => 'fa-lg'], Icon::FA),
                    $url,
                    [
                        'title' => \Yii::t('yii', 'Update'),
                        'data-pjax' => '0',
                    ]
                );
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model) {
                return Html::a(
                    Icon::show('trash-o', ['class' => 'fa-lg'], Icon::FA),
                    $url,
                    [
                        'title' => \Yii::t('yii', 'Delete'),
                        'data-confirm' => \Yii::t('yii', 'Are you sure to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ]
                );
            };
        }
    }
}
