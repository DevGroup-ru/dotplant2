<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%rating_group_object}}".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property string $rating_group
 */
class RatingGroupObject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rating_group_object}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_model_id', 'rating_group'], 'required'],
            [['object_id', 'object_model_id'], 'integer'],
            [['rating_group'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'rating_group' => Yii::t('app', 'Rating Group'),
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['validate'] = $scenarios['default'];
        $scenarios['default'] = [];
        return $scenarios;
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

        return $query->all();
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
