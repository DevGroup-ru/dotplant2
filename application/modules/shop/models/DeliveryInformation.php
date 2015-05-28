<?php

namespace app\modules\shop\models;

use app\models\City;
use app\models\Country;
use Yii;

/**
 * This is the model class for table "{{%delivery_information}}".
 *
 * @property integer $id
 * @property integer $contragent_id
 * @property integer $country_id
 * @property integer $city_id
 * @property string $zip_code
 * @property string $address
 * Relations:
 * @property Contragent $contragent
 * @property Country $country
 * @property City $ity
 */
class DeliveryInformation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_information}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contragent_id'], 'required'],
            [['contragent_id', 'country_id', 'city_id'], 'integer'],
            [['address'], 'string'],
            [['zip_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'contragent_id' => Yii::t('app', 'Contragent ID'),
            'country_id' => Yii::t('app', 'Country ID'),
            'city_id' => Yii::t('app', 'City ID'),
            'zip_code' => Yii::t('app', 'Zip Code'),
            'address' => Yii::t('app', 'Address'),
        ];
    }

    /**
     * @return Contragent|null
     */
    public function getContragent()
    {
        return $this->hasOne(Contragent::className(), ['id' => 'contragent_id']);
    }

    /**
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * @return City|null
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @param Contragent $contragent
     * @return DeliveryInformation|null
     */
    public static function createNewDeliveryInformation(Contragent $contragent, $dummyObject = true)
    {
        $model = new static();
        $model->loadDefaultValues();
        $model->contragent_id = $contragent->id;

        if (!$dummyObject) {
            $model->save();
        }

        return $model;
    }
}
?>