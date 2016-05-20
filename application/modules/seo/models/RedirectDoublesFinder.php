<?php

namespace app\modules\seo\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class RedirectDoublesFinder extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%seo_redirect}}';
    }

    public function attributeLabels()
    {
        return [
            'type' => \Yii::t('app', 'Type'),
            'from' => \Yii::t('app', 'From'),
        ];
    }

    public function findDoubles()
    {

        $query = self::find()
            ->select(['type', 'from', 'COUNT(*) as count'])
            ->groupBy('from')
            ->having('COUNT(*) > 1');

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

