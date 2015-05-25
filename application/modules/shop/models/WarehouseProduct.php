<?php

namespace app\modules\shop\models;

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

    /**
     * @param null $id
     * @param bool $create
     * @return null|\app\models\WarehouseProduct
     */
    public static function findByProductId($id = null, $create = true)
    {
        if (null === $id) {
            return null;
        }

        if (null !== $model = static::findOne(['product_id' => $id])) {
            return $model;
        }

        if (true === $create) {
            $warehouses = Warehouse::activeWarehousesIds();
            if (!empty($warehouses)) {
                $model = new static();
                    $model->warehouse_id = $warehouses[0];
                    $model->product_id = $id;
                    $model->in_warehouse = 0;
                $model->save();

                return $model;
            }
        }

        return null;
    }
}
