<?php


namespace app\modules\user\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_activity}}".
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $user_id
 * @property integer $is_main
 * @property string $last_heartbeat
 */
class UserActivity extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_model_id', 'user_id', 'is_main'], 'required'],
            [['object_id', 'object_model_id', 'user_id', 'is_main'], 'integer'],
            [['last_heartbeat'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'object_model_id' => 'Object Model ID',
            'user_id' => 'User ID',
            'is_main' => 'Is Main',
            'last_heartbeat' => 'Last Heartbeat',
        ];
    }
}
