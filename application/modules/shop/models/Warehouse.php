<?php

namespace app\modules\shop\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%warehouse}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 * @property integer $country_id
 * @property integer $city_id
 * @property string $address
 * @property string $description
 * @property integer $sort_order
 * @property string $map_latitude
 * @property string $map_longitude
 * @property integer $shipping_center
 * @property integer $issuing_center
 * @property string $xml_id
 */
class Warehouse extends \yii\db\ActiveRecord
{
    private static $activeWarehousesIds = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_active', 'country_id', 'city_id', 'sort_order', 'shipping_center', 'issuing_center'], 'integer'],
            [['country_id', 'city_id'], 'required'],
            [['address', 'description'], 'string'],
            [['name', 'map_latitude', 'map_longitude', 'xml_id'], 'string', 'max' => 255]
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
            'is_active' => Yii::t('app', 'Is Active'),
            'country_id' => Yii::t('app', 'Country ID'),
            'city_id' => Yii::t('app', 'City ID'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'map_latitude' => Yii::t('app', 'Map Latitude'),
            'map_longitude' => Yii::t('app', 'Map Longitude'),
            'shipping_center' => Yii::t('app', 'Shipping Center'),
            'issuing_center' => Yii::t('app', 'Issuing Center'),
            'xml_id' => Yii::t('app', 'Xml ID'),
        ];
    }

    /**
    * @inheritdoc
    */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }


    public function afterDelete()
    {
        WarehouseEmail::deleteAll(
            [
                'warehouse_id' => $this->id
            ]
        );

        WarehousePhone::deleteAll(
            [
                'warehouse_id' => $this->id
            ]
        );

        WarehouseOpeninghours::deleteAll(
            [
                'warehouse_id' => $this->id
            ]
        );
        WarehouseProduct::deleteAll(
            [
                'warehouse_id' => $this->id
            ]
        );


        return parent::afterDelete();
    }


    /**
     * Returns array of ID of all active warehouses
     * @return integer[]
     * @throws \Exception
     */
    public static function activeWarehousesIds()
    {
        if (static::$activeWarehousesIds === null) {
            static::$activeWarehousesIds = Warehouse::getDb()->cache(
                function($db) {
                    return Warehouse::find()
                        ->where('is_active=1')
                        ->select('id')
                        ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                        ->asArray()
                        ->column($db);
                },
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(Warehouse::className())
                    ]
                ])
            );
        }
        return static::$activeWarehousesIds;
    }
}
