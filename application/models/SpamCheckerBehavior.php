<?php

namespace app\models;


use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "spam_checker_behavior".
 * @property integer $id
 * @property string $behavior
 * @property string $api_key
 * @property string $name
 * @property string $author_field
 * @property string $content_field
 */
class SpamCheckerBehavior extends \yii\db\ActiveRecord
{
    /**
     * @var int
     */
    public static $enabledApiId = 0;
    private static $field_array_cache = null;
    private static $field_type_array_cache = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'spam_checker_behavior';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['behavior'], 'required'],
            [['behavior'], 'string', 'max' => 255],
            [['api_key', 'name', 'author_field', 'content_field'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'behavior' => Yii::t('app', 'Behavior'),
            'api_key' => Yii::t('app', 'Api Key'),
            'name' => Yii::t('app', 'Name'),
            'author_field' => Yii::t('app', 'Author Field'),
            'content_field' => Yii::t('app', 'Content Field'),
            'enabledApiId' => Yii::t('app', 'Enabled Api Key'),
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'behavior', $this->behavior]);
        $query->andFilterWhere(['like', 'api_key', $this->api_key]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }

    /**
     * Function return array for drop down list
     * @return array
     */
    public static function getAvailableApis()
    {
        static::getEnabledApiId();
        $all = static::find()->all();
        $map = ArrayHelper::map($all, 'id', 'name');
        return ArrayHelper::merge([0 => Yii::t('app', 'Not selected')], $map);
    }

    public static function getEnabledApiId()
    {
        $enabled = Config::getValue('spamCheckerConfig.enabledApiKey', null);
        static::$enabledApiId = static::getApiIdByClassName($enabled);
        return static::$enabledApiId;
    }

    public static function setEnabledApiId($id)
    {
        $config = Config::findOne(['key' => 'enabledApiKey']);
        $model = static::findOne($id);
        if ($model === null) {
            $config->value = 0;
        } else {
            $config->value = $model->behavior;
        }

        $config->save();
        static::$enabledApiId = $id;
    }

    /**
     * Function return id of SpanChecker by behavior class name
     * @param $className string
     * @return int
     */
    public static function getApiIdByClassName($className)
    {
        $model = static::findOne(['behavior' => $className]);
        if ($model === null) {
            $id = 0;
        } else {
            $id = $model->id;
        }
        return $id;
    }

    // А нужны ли нам эти 2 функции???
    public static function getFieldTypesForForm()
    {
        if (static::$field_array_cache === null) {
            $rows = (new Query())->select('id, name')->from(Config::tableName())->all();
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
            $rows = (new Query())->select('id, value')->from(Config::tableName())->where(
                "parent_id=:parent_id",
                [":parent_id" => $parentId]
            )->all();
            static::$field_type_array_cache = [];
            foreach ($rows as $row) {
                static::$field_type_array_cache[$row['id']] = $row['value'];
            }
        }

        return static::$field_type_array_cache;
    }
}
