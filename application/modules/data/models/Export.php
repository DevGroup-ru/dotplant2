<?php

namespace app\modules\data\models;

use app\backgroundtasks\models\Task;
use app\models\Object;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "export".
 *
 * @property integer $user_id
 * @property integer $object_id
 * @property string $filename
 * @property string $status
 * @property string $update_time
 */
class Export extends \yii\db\ActiveRecord
{
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';
    const STATUS_PROCESS = 'process';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%data_export}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'object_id'], 'required'],
            [['user_id', 'object_id', 'update_time'], 'integer'],
            [['filename'], 'string', 'max' => 255],
            [['status'], 'string'],
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
            'filename' => Yii::t('app', 'Export File'),
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
