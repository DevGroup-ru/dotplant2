<?php

namespace app\modules\user\models;
use app\modules\user\models\User;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_service".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $service_type
 * @property string $service_id
 * @property \app\modules\user\models\User $user
 */
class UserService extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['service_type', 'service_id'], 'string', 'max' => 255],
            [
                ['service_type', 'service_id'],
                'unique',
                'targetAttribute' => ['service_type', 'service_id'],
                'message' => 'The combination of Service Type and Service ID has already been taken.'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['user_id', 'service_type', 'service_id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'service_type' => 'Service Type',
            'service_id' => 'Service ID',
        ];
    }

    /**
     * Relation to User model
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
