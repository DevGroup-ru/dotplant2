<?php

namespace app\models;

/**
 * This is the model class for table "layout".
 *
 * @property integer $id
 * @property string $name
 * @property string $layout
 */
class Layout extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%layout}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'layout'], 'required'],
            [['name', 'layout'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'name' => \Yii::t('app', 'Name'),
            'layout' => \Yii::t('app', 'Layout'),
        ];
    }
}
