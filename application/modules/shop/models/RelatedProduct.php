<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%related_product}}".
 *
 * @property integer $product_id
 * @property integer $related_product_id
 */
class RelatedProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%related_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'related_product_id'], 'required'],
            [['product_id', 'related_product_id', 'sort_order',], 'integer'],
            [['product_id', 'related_product_id'], 'unique', 'targetAttribute' => ['product_id', 'related_product_id'], 'message' => 'The combination of Product ID and Related Product ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'related_product_id' => Yii::t('app', 'Related Product ID'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }
}
