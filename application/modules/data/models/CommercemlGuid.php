<?php

namespace app\modules\data\models;

use app\modules\shop\models\Product;
use app\models\Property;
use Yii;

/**
 * This is the model class for table "{{%commerceml_guid}}".
 *
 * @property integer $id
 * @property string $guid
 * @property string $name
 * @property integer $model_id
 * @property string $type
 * Relations:
 * @property Property $property
 * @property Product $product
 */
class CommercemlGuid extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%commerceml_guid}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id'], 'integer'],
            [['type', 'name'], 'string'],
            [['guid'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'guid' => Yii::t('app', 'Guid'),
            'name' => Yii::t('app', 'Name'),
            'model_id' => Yii::t('app', 'Model ID'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * @return Property|null
     */
    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'model_id']);
    }

    /**
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'model_id']);
    }
}
?>