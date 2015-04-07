<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class SpamChecker extends Model
{
    /**
     * This is the model class without table
     * @property string $yandexApiKey
     * @property string $akismetApiKey
     * @property integer $configFieldsParentId
     * @property integer $enabledApiKey;
     */

    public $yandexApiKey;
    public $akismetApiKey;
    public $enabledApiKey;
    public $configFieldsParentId;




    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['yandexApiKey', 'akismetApiKey'], 'string'],
            [['configFieldsParentId', 'enabledApiKey'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'yandexApiKey' => Yii::t('app', 'Yandex API Key'),
            'akismetApiKey' => Yii::t('app', 'Akismet API Key'),
            'enabledApiKey' => Yii::t('app', 'Enabled Api Key'),
            'configFieldsParentId' => Yii::t('app', 'Config Fields Parent Id'),
        ];
    }





    public function getEnabledApiKeyPath()
    {
        $config = new Config();
        $path = $config->findOne(
            [
                'path' => 'spamCheckerConfig.enabledApiKey'
            ]
        );
        if ($path === null) {
            return [];
        }
        $enabledApi = $config->findOne(
            [
                'id' => $path['value']
            ]
        );
        return $enabledApi['path'];
    }
}
