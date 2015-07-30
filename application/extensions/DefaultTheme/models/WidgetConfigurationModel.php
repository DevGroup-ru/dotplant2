<?php

namespace app\extensions\DefaultTheme\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

abstract class WidgetConfigurationModel extends Model
{
    public $header = '';
    public $displayHeader = true;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge($this->thisRules(), [
            [
                [
                    'header',
                ],
                'string',
            ],
            [
                'displayHeader',
                'filter',
                'filter' => 'boolval',
            ],
            [
                'displayHeader',
                'boolean',
            ],
        ]);
    }

    abstract public function thisRules();
}