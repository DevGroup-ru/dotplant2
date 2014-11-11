<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Class Subscribe
 * @package app\models
 *
 * @property integer $id
 * @property string $email
 * @property string $name
 * @property integer $is_active
 * @property string $last_notify
 */
class SubscribeEmail extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%subscribe_email}}';
    }

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['email', 'last_notify'], 'string'],
            [['name'], 'string'],
            [['is_active'], 'integer', 'min' => 0, 'max' => 1],
            ['email', 'email']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'email' => Yii::t('app', 'E-mail'),
            'name' => Yii::t('app', 'Name'),
            'is_active' => Yii::t('app', 'Is active'),
            'last_notify' => Yii::t('app', 'Last notify date')
        ];
    }

    public function getActiveSubscribes()
    {
        return $this->find()->where(['is_active' => '1'])->all();
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
        $query->andFilterWhere(['email' => $this->email]);
        $query->andFilterWhere(['name' => $this->name]);
        $query->andFilterWhere(['is_active' => $this->is_active]);
        $query->andFilterWhere(['last_notify' => $this->last_notify]);
        return $dataProvider;
    }
}
