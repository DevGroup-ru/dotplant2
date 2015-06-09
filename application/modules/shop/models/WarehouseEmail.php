<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%warehouse_email}}".
 *
 * @property integer $id
 * @property integer $warehouse_id
 * @property integer $sort_order
 * @property string $email
 * @property string $name
 */
class WarehouseEmail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order'], 'default', 'value'=> 0],
            [['warehouse_id', 'email'], 'required'],
            [['warehouse_id', 'sort_order'], 'integer'],
            [['email', 'name'], 'string', 'max' => 255]
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
            'email' => Yii::t('app', 'Email'),
            'name' => Yii::t('app', 'Name'),
        ];
    }
}
