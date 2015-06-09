<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%warehouse_openinghours}}".
 *
 * @property integer $id
 * @property integer $warehouse_id
 * @property integer $sort_order
 * @property integer $monday
 * @property integer $tuesday
 * @property integer $wednesday
 * @property integer $thursday
 * @property integer $friday
 * @property integer $saturday
 * @property integer $sunday
 * @property integer $all_day
 * @property string $opens
 * @property string $closes
 * @property string $break_from
 * @property string $break_to
 */
class WarehouseOpeninghours extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_openinghours}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order'], 'default', 'value'=> 0],
            [['warehouse_id', 'sort_order'], 'required'],
            [['warehouse_id', 'sort_order', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'all_day'], 'integer'],
            [['opens', 'closes', 'break_from', 'break_to'], 'string', 'max' => 255]
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
            'monday' => Yii::t('app', 'Monday'),
            'tuesday' => Yii::t('app', 'Tuesday'),
            'wednesday' => Yii::t('app', 'Wednesday'),
            'thursday' => Yii::t('app', 'Thursday'),
            'friday' => Yii::t('app', 'Friday'),
            'saturday' => Yii::t('app', 'Saturday'),
            'sunday' => Yii::t('app', 'Sunday'),
            'all_day' => Yii::t('app', 'All Day'),
            'opens' => Yii::t('app', 'Opens'),
            'closes' => Yii::t('app', 'Closes'),
            'break_from' => Yii::t('app', 'Break From'),
            'break_to' => Yii::t('app', 'Break To'),
        ];
    }
}
