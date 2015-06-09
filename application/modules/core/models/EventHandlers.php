<?php

namespace app\modules\core\models;

use Yii;

/**
 * This is the model class for table "{{%event_handlers}}".
 *
 * @property integer $id
 * @property integer $event_id
 * @property integer $sort_order
 * @property string $handler_class_name
 * @property string $handler_function_name
 * @property integer $is_active
 * @property integer $non_deletable
 * @property string $triggering_type
 */
class EventHandlers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%event_handlers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_id', 'sort_order', 'is_active', 'non_deletable'], 'integer'],
            [['handler_class_name', 'handler_function_name', 'triggering_type'], 'required'],
            [['handler_class_name', 'handler_function_name', 'triggering_type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'event_id' => Yii::t('app', 'Event ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'handler_class_name' => Yii::t('app', 'Handler Class Name'),
            'handler_function_name' => Yii::t('app', 'Handler Function Name'),
            'is_active' => Yii::t('app', 'Is Active'),
            'non_deletable' => Yii::t('app', 'Non Deletable'),
            'triggering_type' => Yii::t('app', 'Triggering Type'),
        ];
    }
}
