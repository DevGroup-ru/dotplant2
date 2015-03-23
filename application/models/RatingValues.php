<?php

namespace app\models;

use Yii;

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
            [['value'], function($attribute, $params){
                $rating = RatingItem::getOneItemByAttributes(['id' => (isset($this->rating_item_id) ? $this->rating_item_id : null)], true);
                $value = intval($this->value);
                if (!empty($rating)) {
                    if ($value > $rating['max_value']) $value = $rating['max_value'];
                    else if ($value < $rating['min_value']) $value = $rating['min_value'];
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
}
