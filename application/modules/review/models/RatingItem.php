<?php

namespace app\modules\review\models;

use app\traits\FindById;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
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
 * @property integer $allow_guest
 * @method RatingItem getById(integer $id)
 */
class RatingItem extends \yii\db\ActiveRecord
{
    use FindById;

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
                'class' => ActiveRecordHelper::className(),
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
            [['min_value', 'max_value', 'step_value', 'require_review', 'allow_guest'], 'integer'],
            [['name', 'rating_group'], 'string', 'max' => 255],
            [['min_value'], 'default', 'value' => 0],
            [['max_value'], 'default', 'value' => 5],
            [['step_value'], 'default', 'value' => 1],
            [['require_review'], 'default', 'value' => 0],
            [['allow_guest'], 'default', 'value' => 0],
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
            'allow_guest' => Yii::t('app', 'Allow guest user to rate'),
        ];
    }

    /**
     * @param bool $isFetched
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]|static
     * @internal param bool $fetch
     */
    public static function getGroupsAll($isFetched = true, $asArray = false)
    {
        $cache_key = 'RatingItem:Groups';
        $cache = Yii::$app->cache->get($cache_key);
        if (true === $isFetched
            && true === $asArray
            && false !== $cache) {
            return $cache;
        }
        $query = static::find()
            ->distinct()
            ->groupBy('rating_group')
            ->orderBy(['rating_group' => SORT_ASC])
            ->asArray(boolval($asArray));
        if (false === $isFetched) {
            return $query;
        }
        $result = $query->all();
        if (true === $asArray) {
            Yii::$app->cache->set(
                $cache_key,
                $result,
                0,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(static::className())
                    ],
                ])
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
            ->where(['rating_group' => $name])
            ->groupBy('rating_group')
            ->orderBy(['rating_group' => SORT_ASC])
            ->asArray();
        return $query->one();
    }

    /**
     * @param array $attributes
     * @param bool $isFetched
     * @param bool $asArray
     * @return array|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     * @internal param bool $fetch
     * @internal param bool $as_array
     */
    public static function getItemsByAttributes($attributes = [], $isFetched = true, $asArray = false)
    {
        if (!is_array($attributes)) {
            return [];
        }
        $cache_key = 'RatingItem:'.Json::encode($attributes);
        $cache = Yii::$app->cache->get($cache_key);
        if (true === $isFetched
            && true === $asArray
            && false !== $cache) {
            return $cache;
        }
        $query = static::find();
        foreach ($attributes as $attr => $value) {
            $query->andWhere([$attr => $value]);
        }
        $query->orderBy(['id' => SORT_ASC])->asArray(boolval($asArray));
        if (false === $isFetched) {
            return $query;
        }
        $result = $query->all();
        if (true === $asArray) {
            Yii::$app->cache->set(
                $cache_key,
                $result,
                0,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(static::className())
                    ],
                ])
            );
        }
        return $result;
    }

    /**
     * @param array $attributes
     * @param bool $asArray
     * @return array|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     * @internal param bool $as_array
     */
    public static function getOneItemByAttributes($attributes = [], $asArray = false)
    {
        $query = static::getItemsByAttributes($attributes, false, $asArray);
        return $query->one();
    }
}