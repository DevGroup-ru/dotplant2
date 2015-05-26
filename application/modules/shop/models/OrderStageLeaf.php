<?php

namespace app\modules\shop\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%order_stage_leaf}}".
 *
 * @property integer $id
 * @property integer $stage_from_id
 * @property integer $stage_to_id
 * @property integer $sort_order
 * @property string $button_label
 * @property string $button_css_class
 * @property integer $notify_buyer
 * @property string $buyer_notification_view
 * @property integer $notify_manager
 * @property string $manager_notification_view
 * @property integer $assign_to_user_id
 * @property string $assign_to_role
 * @property integer $notify_new_assigned_user
 * @property string $role_assignment_policy
 * @property string $event_name
 * Relations:
 * @property OrderStage $stageFrom
 * @property OrderStage $stageTo
 */
class OrderStageLeaf extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_stage_leaf}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stage_from_id', 'stage_to_id', 'button_label'], 'required'],
            [['stage_from_id', 'stage_to_id', 'sort_order', 'notify_buyer', 'notify_manager', 'assign_to_user_id', 'notify_new_assigned_user'], 'integer'],
            [['role_assignment_policy'], 'string'],
            [['button_label', 'button_css_class', 'buyer_notification_view', 'manager_notification_view', 'assign_to_role', 'event_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'stage_from_id' => Yii::t('app', 'Stage From ID'),
            'stage_to_id' => Yii::t('app', 'Stage To ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'button_label' => Yii::t('app', 'Button Label'),
            'button_css_class' => Yii::t('app', 'Button Css Class'),
            'notify_buyer' => Yii::t('app', 'Notify Buyer'),
            'buyer_notification_view' => Yii::t('app', 'Buyer Notification View'),
            'notify_manager' => Yii::t('app', 'Notify Manager'),
            'manager_notification_view' => Yii::t('app', 'Manager Notification View'),
            'assign_to_user_id' => Yii::t('app', 'Assign To User ID'),
            'assign_to_role' => Yii::t('app', 'Assign To Role'),
            'notify_new_assigned_user' => Yii::t('app', 'Notify New Assigned User'),
            'role_assignment_policy' => Yii::t('app', 'Role Assignment Policy'),
            'event_name' => Yii::t('app', 'Event Name'),
        ];
    }

    public function search($params)
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        if ($this->load($params)) {
            $query->andFilterWhere([
                'button_label' => $this->button_label,
                'event_name' => $this->event_name,
            ]);
        }

        return $dataProvider;
    }

    /**
     * @return OrderStage|null
     */
    public function getStageFrom()
    {
        return $this->hasOne(OrderStage::className(), ['id' => 'stage_from_id']);
    }

    /**
     * @return OrderStage|null
     */
    public function getStageTo()
    {
        return $this->hasOne(OrderStage::className(), ['id' => 'stage_to_id']);
    }
}
?>