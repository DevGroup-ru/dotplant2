<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "spam_checker".
 * @property integer $id
 * @property string $behavior
 * @property string $api_key
 * @property string $name
 * @property string $author_field
 * @property string $content_field
 */
class SpamChecker extends \yii\db\ActiveRecord
{
    const FIELD_TYPE_NO_CHECKING = 'notinterpret';
    const FIELD_TYPE_AUTHOR = 'author_field';
    const FIELD_TYPE_CONTENT = 'content_field';

    /**
     * @var int
     */
    public static $enabledApiId = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%spam_checker}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['behavior'], 'required'],
            [['behavior'], 'string', 'max' => 255],
            [['name', 'author_field', 'content_field'], 'string', 'max' => 50],
            [['api_key'], 'string', 'max' => 90]
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
     * Function return array map for drop down list
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
        $enabled = Yii::$app->getModule('core')->spamCheckerApiKey;
        static::$enabledApiId = static::getApiIdByClassName($enabled);
        return static::$enabledApiId;
    }

    public static function setEnabledApiId($id)
    {
        $config = Yii::$app->getModule('core')->spamCheckerApiKey;
        $model = static::findOne($id);
        if ($model === null) {
            $config->value = '';
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

    public static function getFieldTypesForForm()
    {
        return [
            self::FIELD_TYPE_NO_CHECKING => Yii::t('app', 'No'),
            self::FIELD_TYPE_AUTHOR => Yii::t('app', 'Username'),
            self::FIELD_TYPE_CONTENT => Yii::t('app', 'Content'),
        ];
    }

    /**
     * @return SpamChecker
     */
    public static function getActive()
    {
        return static::findOne(static::getEnabledApiId());
    }
}
