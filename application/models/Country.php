<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%country}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $iso_code
 * @property integer $sort_order
 * @property string $slug
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order'], 'integer'],
            [['name', 'iso_code', 'slug'], 'string', 'max' => 255]
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
            'iso_code' => Yii::t('app', 'Iso Code'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'slug' => Yii::t('app', 'Slug'),
        ];
    }
}
?>