<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class ErrorMonitor
 * @package app\models
 *
 * This is the model class for table "error_log".
 *
 * @property integer $id
 * @property integer $url_id
 * @property integer $http_code
 * @property string $info
 * @property integer $timestamp
 * @property string $server_vars
 * @property string $request_vars
 */
class ErrorLog extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%error_log}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'timestamp',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['id', 'url_id', 'http_code', 'timestamp'], 'integer'],
            [['info', 'server_vars', 'request_vars'], 'string']
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['id', 'url_id', 'http_code', 'info', 'server_vars', 'request_vars'],
            'search' => ['id', 'url_id', 'http_code', 'info', 'server_vars', 'request_vars'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url_id' => Yii::t('app', 'URL ID'),
            'http_code' => Yii::t('app', 'HTTP response code'),
            'info' => Yii::t('app', 'Information'),
            'timestamp' => Yii::t('app', 'Timestamp'),
            'server_vars' => Yii::t('app', 'Server variables'),
            'request_vars' => Yii::t('app', 'Request variables'),
        ];
    }

    public function getErrorUrl()
    {
        return $this->hasOne(ErrorUrl::className(), ['id' => 'url_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $numberElementsToStore = Yii::$app->getModule('core')->numberElementsToStore - 1;
        $ids = static::find()->select('`id`')->where(['url_id' => $this->url_id])->orderBy('`id` DESC')
            ->offset($numberElementsToStore)->column();
        if (count($ids) > 0) {
            static::deleteAll(['in', '`id`', $ids]);
        }
        return true;
    }
}
