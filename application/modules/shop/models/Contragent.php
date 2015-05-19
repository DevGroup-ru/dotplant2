<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%contragent}}".
 *
 * @property integer $id
 * @property string $type
 * Relations:
 * @property Customer[] $customers
 */
class Contragent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contragent}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['contragent_id' => 'id']);
    }
}
?>