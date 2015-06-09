<?php

namespace app\modules\review\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%rating_values}}".
 *
 * @property integer $id
 * @property string $rating_id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $rating_item_id
 * @property integer $value
 * @property integer $user_id
 * @property string $date
 */
class RatingValues extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rating_values}}';
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
            [['rating_id', 'object_id', 'object_model_id', 'rating_item_id', 'date'], 'required'],
            [['object_id', 'object_model_id', 'rating_item_id', 'value', 'user_id'], 'integer'],
            [['user_id'], 'default', 'value' => 0],
            [['date'], 'safe'],
            [['rating_id'], 'string', 'max' => 255],
            [['value'], function($attribute, $params) {
                $rating = RatingItem::getOneItemByAttributes(
                    ['id' => (isset($this->rating_item_id) ? $this->rating_item_id : null)],
                    true
                );
                $value = intval($this->value);
                if (!empty($rating)) {
                    if ($value > $rating['max_value']) {
                        $value = $rating['max_value'];
                    } elseif ($value < $rating['min_value']) {
                        $value = $rating['min_value'];
                    }
                }
                $this->value = $value;
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'rating_id' => Yii::t('app', 'Rating ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'rating_item_id' => Yii::t('app', 'Rating Item ID'),
            'value' => Yii::t('app', 'Value'),
            'user_id' => Yii::t('app', 'User ID'),
            'date' => Yii::t('app', 'Date'),
        ];
    }

    /**
     * @param ActiveRecord $objectModel
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getValuesByObjectModel($objectModel)
    {
        if (!is_object($objectModel) || !$objectModel instanceof ActiveRecord || is_null($objectModel->object)) {
            return [];
        }
        $cacheKey = "RatingValues:{$objectModel->object->id}:{$objectModel->id}";
        $result = Yii::$app->cache->get($cacheKey);
        if (false === $result) {
            $query = static::find();
            $query->select('rating_item_id, value')
                ->from(static::tableName() . ' rv')
                ->where(['rv.object_id' => $objectModel->object->id, 'rv.object_model_id' => $objectModel->id])
                ->join(
                    'INNER JOIN',
                    Review::tableName() . ' r',
                    'r.rating_id = rv.rating_id AND r.status = :status',
                    [':status' => Review::STATUS_APPROVED]
                );
            $rows = $query->all();
            $result = [];
            foreach ($rows as $row) {
                if (isset($result[$row['rating_item_id']])) {
                    $result[$row['rating_item_id']][] = $row['value'];
                } else {
                    $result[$row['rating_item_id']] = [$row['value']];
                }
            }
            Yii::$app->cache->set(
                $cacheKey,
                $result,
                0,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getObjectTag(
                                static::className(),
                                "{$objectModel->object->id}:{$objectModel->id}"
                            )
                        ],
                    ]
                )
            );
        }
        return $result;
    }
}
