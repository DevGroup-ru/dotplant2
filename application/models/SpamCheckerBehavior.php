<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "spam_checker_behavior".
 * @property integer $id
 * @property string $behavior
 * @property string $api_key
 * @property string $name
 * @property string $author_field
 * @property string $content_field
 */
class SpamCheckerBehavior extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'spam_checker_behavior';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['behavior'], 'required'],
            [['behavior'], 'string', 'max' => 255],
            [['api_key', 'name', 'author_field', 'content_field'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'behavior' => Yii::t('app', 'Behavior'),
            'api_key' => Yii::t('app', 'Api Key'),
            'name' => Yii::t('app', 'Name'),
            'author_field' => Yii::t('app', 'Author Field'),
            'content_field' => Yii::t('app', 'Content Field'),
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
        $query->andFilterWhere(['like', 'behavior', $this->behavior]);
        $query->andFilterWhere(['like', 'api_key', $this->api_key]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}
