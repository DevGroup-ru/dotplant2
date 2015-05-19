<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $contragent_id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * Relations:
 * @property Contragent[] $contragents
 */
class Customer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'contragent_id'], 'integer'],
            [['contragent_id'], 'required'],
            [['first_name', 'middle_name', 'last_name', 'email', 'phone'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'contragent_id' => Yii::t('app', 'Contragent ID'),
            'first_name' => Yii::t('app', 'First Name'),
            'middle_name' => Yii::t('app', 'Middle Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'email' => Yii::t('app', 'Email'),
            'phone' => Yii::t('app', 'Phone'),
        ];
    }

    public function getContragents()
    {
        return $this->hasMany(Contragent::className(), ['id' => 'contragent_id']);
    }

    public static function getCustomerByUserId($id = null)
    {
        return static::findOne(['user_id' => $id]);
    }
}
?>