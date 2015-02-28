<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%city}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort_order
 * @property string $slug
 * @property integer $country_id
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order', 'country_id'], 'integer'],
            [['country_id'], 'required'],
            [['name', 'slug'], 'string', 'max' => 255]
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
            'sort_order' => Yii::t('app', 'Sort Order'),
            'slug' => Yii::t('app', 'Slug'),
            'country_id' => Yii::t('app', 'Country ID'),
        ];
    }
}
