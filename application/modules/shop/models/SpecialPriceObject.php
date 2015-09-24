<?php

namespace app\modules\shop\models;

use Yii;
use yii\base\InvalidParamException;
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

    /**
     * @return SpecialPriceList|null
     */
    public function getSpecialPriceList()
    {
        return $this->hasOne(SpecialPriceList::className(), ['id' => 'special_price_list_id']);
    }

    public function isDiscount($type = null)
    {
        return $this->specialPriceList->type === "discount";
    }

    /**
     * @param integer $special_price_list_id
     * @param integer $object_model_id
     * @param float $price
     * @param string $name
     */
    public static function setObject($special_price_list_id, $object_model_id, $price, $name)
    {
        $object = static::findOne([
            'special_price_list_id' => $special_price_list_id,
            'object_model_id' => $object_model_id
        ]);
        if (null === $object) {
            $object = new static();
            $object->special_price_list_id = $special_price_list_id;
            $object->object_model_id = $object_model_id;
        }
        $object->price = $price;
        $object->name = $name;
        $object->save();
    }

    /**
     * @param integer $object_model_id
     * @param string $type
     * @return string
     */
    public static function getSumPrice($object_model_id, $type)
    {
        $objects = static::find()
            ->select('price')
            ->where(
                [
                    'special_price_list_id' => ArrayHelper::map(SpecialPriceList::getModelsByKey($type), 'id', 'id'),
                    'object_model_id' => $object_model_id
                ]
            )
            ->asArray()
            ->all();

        return array_reduce($objects,
            function ($result, $item) {
                return $result += $item['price'];
            }, 0);
    }

    /**
     * @param $model
     * @return null
     * @throws \Exception
     */
    public static function deleteAllByObject($model)
    {
        if (!isset($model->object) || empty($model->object)) {
            return null;
        }
        $modelsFind = static::find()
            ->joinWith(['specialPriceList'])
            ->where(
                [
                    SpecialPriceList::tableName() . '.object_id' => $model->object->id,
                    static::tableName() . '.object_model_id' => $model->id
                ]
            );
        foreach ($modelsFind->all() as $objectPrice) {
            $objectPrice->delete();
        }

    }
}
