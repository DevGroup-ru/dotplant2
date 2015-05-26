<?php

namespace app\modules\shop\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%special_price_object}}".
 *
 * @property integer $id
 * @property integer $special_price_list_id
 * @property integer $object_model_id
 * @property double $price
 * @property string $name
 * @property SpecialPriceList $specialPriceList
 */
class SpecialPriceObject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%special_price_object}}';
    }

    public function getSpecialPriceList()
    {
        return $this->hasOne(SpecialPriceList::className(), ['id'=>'special_price_list_id']);
    }



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['special_price_list_id', 'object_model_id', 'name'], 'required'],
            [['special_price_list_id', 'object_model_id'], 'integer'],
            [['price'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'special_price_list_id' => Yii::t('app', 'Special Price List ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'price' => Yii::t('app', 'Price'),
        ];
    }

    public static function setObject($special_price_list_id, $object_model_id, $price, $name)
    {
        $object = self::find()
            ->where(
                [
                    'special_price_list_id' => $special_price_list_id,
                    'object_model_id' => $object_model_id
                ]
            )
            ->one();

        if (!$object) {
            $object = new SpecialPriceObject();
            $object->special_price_list_id = $special_price_list_id;
            $object->object_model_id = $object_model_id;
        }
        $object->price = $price;
        $object->name = $name;
        $object->save();

    }

    public static function getSumPrice($object_model_id, $type)
    {
        $sum = 0;

        $objects = self::find()
            ->select('price')
            ->where(
                [
                    'special_price_list_id' => ArrayHelper::map(SpecialPriceList::getModelsByKey($type), 'id', 'id'),
                    'object_model_id' => $object_model_id
                ]
            )
            ->all();

        foreach ($objects as $object) {
            $sum += $object->price;
        }

        return $sum;
    }
}
