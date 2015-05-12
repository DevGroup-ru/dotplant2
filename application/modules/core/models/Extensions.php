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
 * This is the model class for table "{{%extensions}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 * @property string $packagist_name
 * @property string $force_version
 * @property integer $type
 * @property string $latest_version
 * @property string $current_package_version_timestamp
 * @property string $latest_package_version_timestamp
 * @property string $homepage
 * @property string $namespace_prefix
 */
class Extensions extends \yii\db\ActiveRecord
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
        return '{{%extensions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'packagist_name', 'namespace_prefix'], 'required'],
            [['is_active', 'type'], 'integer'],
            [['current_package_version_timestamp', 'latest_package_version_timestamp'], 'safe'],
            [['name', 'packagist_name', 'force_version', 'latest_version', 'homepage', 'namespace_prefix'], 'string', 'max' => 255]
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
            'is_active' => Yii::t('app', 'Is Active'),
            'packagist_name' => Yii::t('app', 'Packagist Name'),
            'force_version' => Yii::t('app', 'Force Version'),
            'type' => Yii::t('app', 'Type'),
            'latest_version' => Yii::t('app', 'Latest Version'),
            'current_package_version_timestamp' => Yii::t('app', 'Current Package Version Timestamp'),
            'latest_package_version_timestamp' => Yii::t('app', 'Latest Package Version Timestamp'),
            'homepage' => Yii::t('app', 'Homepage'),
            'namespace_prefix' => Yii::t('app', 'Namespace Prefix'),
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
        $query->andFilterWhere(['is_active' => $this->is_active]);
        $query->andFilterWhere(['type' => $this->type]);
        $query->andFilterWhere(['latest_version' => $this->latest_version]);

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'packagist_name', $this->packagist_name]);
        $query->andFilterWhere(['like', 'force_version', $this->force_version]);
        $query->andFilterWhere(['like', 'current_package_version_timestamp', $this->current_package_version_timestamp]);
        $query->andFilterWhere(['like', 'latest_package_version_timestamp', $this->latest_package_version_timestamp]);
        $query->andFilterWhere(['like', 'homepage', $this->homepage]);
        $query->andFilterWhere(['like', 'namespace_prefix', $this->namespace_prefix]);

        return $dataProvider;
    }

    /**
     * @return ExtensionTypes Instance of corresponding ExtensionTypes model
     */
    public function getExtensionType()
    {
        return ExtensionTypes::findById($this->type);
    }
}
