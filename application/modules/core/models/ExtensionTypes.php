<?php

namespace app\modules\core\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\db\ActiveQuery;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%extension_types}}".
 *
 * @property integer $id
 * @property string $name
 */
class ExtensionTypes extends \yii\db\ActiveRecord
{
    private static $identity_map = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%extension_types}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * Returns model instance by ID using IdentityMap
     * @param integer $id
     * @return Object
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            static::$identity_map[$id] = Yii::$app->cache->get(static::tableName() . ': ' . $id);
            if (static::$identity_map[$id] === false) {
                static::$identity_map[$id] = static::findOne($id);
                if (is_object(static::$identity_map[$id])) {
                    Yii::$app->cache->set(
                        static::tableName() . ': ' . $id,
                        static::$identity_map[$id],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $id),
                                ],
                            ]
                        )
                    );
                }
            }
        }
        return static::$identity_map[$id];
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
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}
