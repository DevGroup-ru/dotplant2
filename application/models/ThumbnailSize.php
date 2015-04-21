<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "thumbnail_size".
 * @property integer $id
 * @property integer $width
 * @property integer $height
 * @property integer $default_watermark_id
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
            [['width', 'height', 'default_watermark_id'], 'integer']
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
            'default_watermark_id' => Yii::t('app', 'Default watermark id'),
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
        $query->andFilterWhere(['width' => $this->width]);
        $query->andFilterWhere(['height' => $this->height]);
        return $dataProvider;
    }

    public static function getByDemand($demand)
    {
        $sizes = explode('x', $demand);
        $size = static::findOne(['width' => $sizes[0], 'height' => $sizes[1]]);
        if ($size === null) {
            $size = new ThumbnailSize;
            $size->setAttributes(['width' => $sizes[0], 'height' => $sizes[1]]);
            $size->save();
        }
        return $size;
    }
}
