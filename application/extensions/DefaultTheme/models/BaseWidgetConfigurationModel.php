<?php

namespace app\extensions\DefaultTheme\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class BaseWidgetConfigurationModel extends WidgetConfigurationModel
{
    public $configurationJson = '{}';

    public function loadState($json)
    {
        if (!is_array($json)) {
            return;
        }
        if (isset($json['header'])) {
            $this->header = $json['header'];
            unset($json['header']);
        }
        if (isset($json['displayHeader'])) {
            $this->displayHeader = $json['displayHeader'];
            unset($json['displayHeader']);
        }
        $this->configurationJson = $json;
    }

    /**
     * @inheritdoc
     */
    public function thisRules()
    {
        return [
            [
                [
                    'configurationJson',
                ],
                'string',
            ],
        ];
    }
}