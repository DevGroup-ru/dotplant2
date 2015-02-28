<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%warehouse_product}}".
 *
 * @property integer $id
 * @property integer $warehouse_id
 * @property integer $product_id
 * @property double $in_warehouse
 * @property double $reserved_count
 * @property string $sku
 * @property Warehouse $warehouse
 */
class WarehouseProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'product_id'], 'required'],
            [['warehouse_id', 'product_id'], 'integer'],
            [['in_warehouse', 'reserved_count'], 'number'],
            [['sku'], 'string', 'max' => 255],
            [['in_warehouse', 'reserved_count'], 'default', 'value' => 0],
            [['warehouse_id', 'product_id'], 'unique', 'targetAttribute' => ['warehouse_id', 'product_id'], 'message' => 'The combination of Warehouse ID and Product ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'warehouse_id' => Yii::t('app', 'Warehouse ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'in_warehouse' => Yii::t('app', 'In Warehouse'),
            'reserved_count' => Yii::t('app', 'Reserved Count'),
            'sku' => Yii::t('app', 'Sku'),
        ];
    }

    /**
     * Relation to Warehouse
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::className(), ['id' => 'warehouse_id']);
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
}
