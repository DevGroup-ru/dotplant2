<?php

namespace app\backend\columns;

use Yii;
use app;
use yii\helpers\ArrayHelper;

/**
 * Standard action column for backend grid - used for most common cases
 * @package app\backend\columns
 */
class ActionColumn extends app\backend\components\ActionColumn
{
    public $options = [
        'width' => '95px',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->buttons = [
            [
                'url' => 'edit',
                'icon' => 'pencil',
                'class' => 'btn-primary',
                'label' => Yii::t('app', 'Edit'),

            ],
            [
                'url' => 'delete',
                'icon' => 'trash-o',
                'class' => 'btn-danger',
                'label' => Yii::t('app', 'Delete'),
            ],
        ];

    }
}