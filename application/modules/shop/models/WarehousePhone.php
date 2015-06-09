<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%warehouse_phone}}".
 *
 * @property integer $id
 * @property integer $warehouse_id
 * @property integer $sort_order
 * @property string $phone
 * @property string $name
 */
class WarehousePhone extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_phone}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order'], 'default', 'value'=> 0],
            [['warehouse_id', 'phone'], 'required'],
            [['warehouse_id', 'sort_order'], 'integer'],
            [['phone', 'name'], 'string', 'max' => 255]
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
            'sort_order' => Yii::t('app', 'Sort Order'),
            'phone' => Yii::t('app', 'Phone'),
            'name' => Yii::t('app', 'Name'),
        ];
    }
}
