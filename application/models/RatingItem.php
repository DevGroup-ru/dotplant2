<?php

namespace app\models;

use Yii;
use yii\caching\TagDependency;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%rating_item}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $rating_group
 * @property integer $min_value
 * @property integer $max_value
 * @property integer $step_value
 * @property integer $require_review
 */
class RatingItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rating_item}}';
    }

    /**
     * @return array
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
    public function rules()
    {
        return [
            [['name', 'rating_group'], 'required'],
            [['min_value', 'max_value', 'step_value', 'require_review'], 'integer'],
            [['name', 'rating_group'], 'string', 'max' => 255],
            [['min_value'], 'default', 'value' => 0],
            [['max_value'], 'default', 'value' => 5],
            [['step_value'], 'default', 'value' => 1],
            [['require_review'], 'default', 'value' => 0],
            [['max_value'], 'compare', 'compareAttribute' => 'min_value', 'operator' => '>'],
            [['step_value', 'min_value'], 'compare', 'compareAttribute' => 'max_value', 'operator' => '<'],
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
            'rating_group' => Yii::t('app', 'Rating Group'),
            'min_value' => Yii::t('app', 'Min Value'),
            'max_value' => Yii::t('app', 'Max Value'),
            'step_value' => Yii::t('app', 'Step Value'),
            'require_review' => Yii::t('app', 'Require Review'),
        ];
    }

    /**
     * @param bool $fetch
     * @return array|\yii\db\ActiveRecord[]|static
     */
    public static function getGroupsAll($fetch = true, $as_array = false)
    {
        $cache_key = 'RatingItem:Groups';
        if ((true === $fetch) && (true === $as_array) && (false !== $cache = Yii::$app->cache->get($cache_key))) {
            return $cache;
        }

        $query = static::find()
            ->distinct()
            ->groupBy('rating_group')
            ->orderBy(['rating_group' => SORT_ASC]);

        if (true === $as_array) {
            $query->asArray();
        }

        if (false === $fetch) {
            return $query;
        }

        $result = $query->all();
        if (true === $as_array) {
            Yii::$app->cache->set(
                $cache_key,
                $result,
                0,
                new TagDependency(
                    [
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                        ],
                    ]
                )
            );
        }
        return $result;
    }

    /**
     * @param null $name
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getGroupByName($name = null)
    {
        $query = static::find()
            ->distinct()
            ->where(['like', 'rating_group', $name])
            ->groupBy('rating_group')
            ->orderBy(['rating_group' => SORT_ASC])
            ->asArray();

        return $query->one();
    }

    /**
     * @param array $attributes
     * @param bool $fetch
     * @param bool $as_array
     * @return array|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function getItemsByAttributes($attributes = [], $fetch = true, $as_array = false)
    {
        if (empty($attributes) && !is_array($attributes)) {
            return [];
        }

        $attributes_exists = array_intersect(static::attributes(), array_keys($attributes));
        if (empty($attributes_exists)) {
            return [];
        }

        $cache_key = 'RatingItem:'.Json::encode($attributes);
        if ((true === $fetch) && (true === $as_array) && (false !== $cache = Yii::$app->cache->get($cache_key))) {
            return $cache;
        }

        $query = static::find();
        foreach ($attributes_exists as $attr) {
            $query->andWhere([$attr => $attributes[$attr]]);
        }
        $query->orderBy(['id' => SORT_ASC]);

        if (true === $as_array) {
            $query->asArray();
        }

        if (false === $fetch) {
            return $query;
        }

        $result = $query->all();
        if (true === $as_array) {
            Yii::$app->cache->set(
                $cache_key,
                $result,
                0,
                new TagDependency(
                    [
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                        ],
                    ]
                )
            );
        }
        return $result;
    }

    /**
     * @param array $attributes
     * @param bool $as_array
     * @return array|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function getOneItemByAttributes($attributes = [], $as_array = false)
    {
        $query = static::getItemsByAttributes($attributes, false);

        if (true === $as_array) {
            $query->asArray();
        }

        return $query->one();
    }
}
