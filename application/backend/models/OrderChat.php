<?php

namespace app\backend\models;

use app\modules\user\models\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_chat".
 *
 * @property string $id
 * @property string $order_id
 * @property string $user_id
 * @property string $date
 * @property string $message
 * Relations:
 * @property User $user
 */
class OrderChat extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_chat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'message'], 'required'],
            [['order_id', 'user_id'], 'integer'],
            [['date'], 'safe'],
            [['message'], 'string'],
            [['user_id'], 'default', 'value' => \Yii::$app->user->id],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'date' => Yii::t('app', 'Date'),
            'message' => Yii::t('app', 'Message'),
        ];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeSave($insert)
    {
        if (false === parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->message)) {
            return false;
        }

        return true;
    }
}
?>