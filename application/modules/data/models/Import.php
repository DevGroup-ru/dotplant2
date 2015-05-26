<?php

namespace app\modules\data\models;

use app\models\Object;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%data_import}}".
 *
 * @property integer $user_id
 * @property integer $object_id
 * @property string $filename
 * @property string $status
 * @property integer $update_time
 */
class Import extends \yii\db\ActiveRecord
{
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';
    const STATUS_PROCESS = 'process';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%data_import}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'object_id'], 'required'],
            [['user_id', 'object_id', 'update_time'], 'integer'],
            [['status'], 'string'],
            [['filename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'filename' => Yii::t('app', 'Filename'),
            'status' => Yii::t('app', 'Status'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'update_time',
                'updatedAtAttribute' => 'update_time',
            ],
        ];
    }

    public function getObject()
    {
        return $this->hasOne(Object::className(), ['id' => 'object_id']);
    }
}
