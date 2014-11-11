<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Class ErrorUrl
 * @package app\models
 *
 * This is the model class for table "error_url".
 *
 * @property integer $id
 * @property string $url
 * @property integer $immediate_notify_count
 */
class ErrorUrl extends ActiveRecord
{
    public $date;

    public static function tableName()
    {
        return '{{%error_url}}';
    }

    public function rules()
    {
        return [
            [['id', 'immediate_notify_count'], 'integer'],
            [['url'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'URL'),
            'immediate_notify_count' => Yii::t('app', 'Immediate notify count')
        ];
    }

    public function getErrorLog()
    {
        return $this->hasMany(ErrorLog::className(), ['url_id' => 'id']);
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['url' => $this->url]);
        return $dataProvider;
    }
}
