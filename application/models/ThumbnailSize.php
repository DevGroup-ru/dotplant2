<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "thumbnail_size".
 *
 * @property integer $id
 * @property integer $width
 * @property integer $height
 */
class ThumbnailSize extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%thumbnail_size}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['width', 'height'], 'required'],
            [['width', 'height'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /** @var $query \yii\db\ActiveQuery */
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
        $query->andFilterWhere(['width' => $this->id]);
        $query->andFilterWhere(['height' => $this->id]);
        return $dataProvider;
    }
}
