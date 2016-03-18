<?php

namespace app\modules\shop\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%measure}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $symbol
 * @property double $nominal
 * @todo Implement value format
 */
class Measure extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%measure}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'symbol', 'nominal'], 'required'],
            [['nominal'], 'number'],
            [['name', 'symbol'], 'string', 'max' => 255],
            [['sort_order'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'symbol' => Yii::t('app', 'Symbol'),
            'nominal' => Yii::t('app', 'Nominal'),
        ];
    }

    /**
     * Get measure by id.
     * @param int $id
     * @return Measure
     */
    public static function findById($id)
    {
        $cacheKey = 'Measure: ' . $id;
        $measure = Yii::$app->cache->get($cacheKey);
        if ($measure === false) {
            $measure = self::findOne($id);
            if (is_null($measure)) {
                return null;
            }
            Yii::$app->cache->set(
                $cacheKey,
                $measure,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(self::className()),
                            ActiveRecordHelper::getObjectTag(self::className(), $id),
                        ],
                    ]
                )
            );
        }
        return $measure;
    }

    /**
     * Round up a quantity.
     * @param float $quantity
     * @return float
     */
    public function ceilQuantity($quantity)
    {
        if (Yii::$app->getModule('shop')->useCeilQuantity) {
            $accuracy = 1000000; // var_dump(round(16.8/1.2)==(16.8/1.2)); false
            $nQuantity = floor($quantity * $accuracy);
            $nominal = floor($this->nominal * $accuracy);

            if ($nQuantity % $nominal !== 0) {
                $quantity = ceil($nQuantity / $nominal) * $this->nominal;
            }
        }
        return round($quantity, 6);
    }
}
