<?php

namespace app\models;

use app\traits\LoadModel;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "view".
 *
 * @property integer $id
 * @property string $name
 * @property string $view
 */
class View extends ActiveRecord
{
    private static $identity_map = null;

    use LoadModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%view}}';
    }

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
    public function rules()
    {
        return [
            [['name', 'view', 'category', 'internal_name'], 'string'],
            [['name', 'view'], 'required'],
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
            'view' => Yii::t('app', 'View'),
            'category' => Yii::t('app', 'Category'),
            'internal_name' => Yii::t('app', 'Internal name'),
        ];
    }

    /**
     * Поиск
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query = self::find(),
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'category', $this->category]);
        return $dataProvider;
    }

    /**
     * @param null $id
     * @return string|null
     */
    public static function getViewById($id = null)
    {
        if (null === $id) {
            return null;
        }
        if (null === static::$identity_map) {
            $cacheKey = static::className().'ModelMap';
            if (false === $map = Yii::$app->cache->get($cacheKey)) {
                if (null === $_model = static::find()->asArray()->all()) {
                    $map = [];
                } else {
                    foreach ($_model as $v) {
                        $map[$v['id']] = $v['view'];
                    }
                }
            }
            Yii::$app->cache->set(
                $cacheKey,
                static::$identity_map = $map,
                0,
                new TagDependency(
                    [
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className()),
                        ],
                    ]
                )
            );
        }
        return (array_key_exists($id, static::$identity_map) && !empty(static::$identity_map[$id]))
            ? static::$identity_map[$id]
            : null;
    }

    /**
     * @return array
     */
    public static function getAllAsArray()
    {
        if (null === $model = static::find()->asArray()->all()) {
            return [];
        }
        $result = [0 => Yii::t('app', 'Parent')];
        foreach ($model as $item) {
            $result[$item['id']] = $item['name'];
        }
        return $result;
    }

    /*
     *
     */
    public function beforeDelete()
    {
        ViewObject::deleteByViewId($this->id);
        return parent::beforeDelete();
    }
}
