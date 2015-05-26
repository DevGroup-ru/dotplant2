<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%special_price_list_type}}".
 *
 * @property integer $id
 * @property string $key
 * @property string $description
 */
class SpecialPriceListType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%special_price_list_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key', 'description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'key' => Yii::t('app', 'Key'),
            'description' => Yii::t('app', 'Description'),
        ];
    }
}
