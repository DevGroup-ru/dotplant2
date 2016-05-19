<?php

namespace app\modules\image\models;

use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "thumbnail_size".
 * @property integer $id
 * @property integer $width
 * @property integer $height
 * @property integer $quality
 * @property integer $default_watermark_id
 * @property string $resize_mode
 */
class ThumbnailSize extends ActiveRecord
{
    const RESIZE = 'resize';

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
            [['resize_mode'], 'default', 'value' => ManipulatorInterface::THUMBNAIL_INSET],
            [['width', 'height', 'quality'], 'required'],
            [['width', 'height', 'default_watermark_id', 'quality'], 'integer'],
            [['quality'], 'number', 'min' => 0, 'max' => 100],
            [['resize_mode'], 'string'],
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
            'quality' => Yii::t('app', 'Quality'),
            'default_watermark_id' => Yii::t('app', 'Default watermark id'),
            'resize_mode' => Yii::t('app', 'Resize mode'),
        ];
    }

    /**
     *
     * @return array
     */
    public static function getResizeModes()
    {
        return [
            ManipulatorInterface::THUMBNAIL_INSET => Yii::t('app', 'Inset'),
            ManipulatorInterface::THUMBNAIL_OUTBOUND => Yii::t('app', 'Outbound'),
            static::RESIZE => Yii::t('app', 'Resize'),
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
            $size->loadDefaultValues();
            $size->setAttributes(['width' => $sizes[0], 'height' => $sizes[1]]);
            $size->save();
        }
        return $size;
    }
}
