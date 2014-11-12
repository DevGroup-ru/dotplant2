<?php

namespace app\backend\models;

use app\models\ErrorLog;
use app\models\ErrorUrl;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * Class ErrorMonitor
 * @package app\backend\models
 * @property string url
 * @property integer $http_code
 * @property string $event_date
 * @property string $server_vars
 * @property string $request_vars
 * @property string $info
 * @property integer $timestamp
 */
class ErrorMonitor extends Model
{
    public $url;
    public $http_code;
    public $info;
    public $event_date;
    public $server_vars;
    public $request_vars;
    public $timestamp;

    public function rules()
    {
        return [
            [['http_code', 'timestamp'], 'integer'],
            [['url', 'info', 'event_date', 'server_vars', 'request_vars'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'url' => Yii::t('app', 'URL'),
            'http_code' => Yii::t('app', 'HTTP code'),
            'info' => Yii::t('app', 'Info'),
            'event_date' => Yii::t('app', 'Event date'),
            'server_vars' => Yii::t('app', 'Server variables'),
            'request_vars' => Yii::t('app', 'Request variables'),
            'timestamp' => Yii::t('app', 'Timestamp')
        ];
    }


    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = (new Query())
            ->select(
                [
                    'log.http_code',
                    '(SELECT FROM_UNIXTIME(log.timestamp)) AS event_date', 'log.id', 'log.server_vars',
                    'log.request_vars',
                    'log.info',
                    'url.url'
                ]
            )->from([ErrorLog::tableName() . " AS log", ErrorUrl::tableName() . " AS url"]);

        $this->load($params);

        $query->andFilterWhere(['log.http_code' => $this->http_code]);
        $query->andFilterWhere(['log.timestamp' => $this->timestamp]);
        $query->andFilterWhere(['url.url' => $this->url]);
        $query->andFilterWhere(['log.info' => $this->info]);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );

        return $dataProvider;
    }
}
