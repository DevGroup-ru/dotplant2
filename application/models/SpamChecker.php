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
     *
     * @property string $yandexApiKey
     * @property string $akismetApiKey
     * @property integer $configFieldsParentId
     * @property integer $enabledApiKey;
     */

    public $yandexApiKey;
    public $akismetApiKey;
    public $enabledApiKey;
    public $configFieldsParentId;

    private static $field_array_cache = null;
    private static $field_type_array_cache = null;

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

    public static function getAvailableApis()
    {
        $config = Config::findOne(
            [
                'path' => 'spamCheckerConfig.apikeys',
            ]
        );
        if ($config === null) {
            return [];
        }
        return ArrayHelper::map($config->children, 'id', 'name');
    }

    public static function getFieldTypesForForm()
    {
        if (static::$field_array_cache === null) {
            $rows = (new Query())
                ->select('id, name')
                ->from(Config::tableName())
                ->all();
            static::$field_array_cache = [];
            foreach ($rows as $row) {
                static::$field_array_cache[$row['id']] = $row['name'];
            }
        }

        return static::$field_array_cache;
    }

    public static function getFieldTypesForFormByParentId($parentId = 0)
    {
        if (static::$field_type_array_cache === null) {
            $rows = (new Query())
                ->select('id, value')
                ->from(Config::tableName())
                ->where("parent_id=:parent_id", [":parent_id" => $parentId])
                ->all();
            static::$field_type_array_cache = [];
            foreach ($rows as $row) {
                static::$field_type_array_cache[$row['id']] = $row['value'];
            }
        }

        return static::$field_type_array_cache;
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
