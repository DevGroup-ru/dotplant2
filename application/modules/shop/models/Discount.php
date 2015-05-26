<?php

namespace app\modules\shop\models;

use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;

/**
 * This is the model class for table "{{%discount}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $appliance
 * @property double $value
 * @property integer $value_in_percent
 * @property double $apply_order_price_lg
 */
class Discount extends \yii\db\ActiveRecord
{

    public $options = [];
    public $applianceValues = [];


    public function init()
    {
        $this->applianceValues = [
            'order_without_delivery' => Yii::t('app', 'Order without delivery'),
            'order_with_delivery' => Yii::t('app', 'Order with delivery'),
            'products' => Yii::t('app', 'Products'),
            'delivery' => Yii::t('app', 'Delivery'),
        ];
        return parent::init();
    }


    public function getDiscountPrice($price, $deliveryPrice = 0)
    {
        $discountPrice = 0;

        switch ($this->appliance) {
            case 'order_without_delivery':
                $discountPrice = $price;
                break;
            case 'order_with_delivery':
                $discountPrice = $price + $deliveryPrice;
                break;
            case 'delivery':
                $discountPrice = $deliveryPrice;
                break;
            case 'products':
                $discountPrice = $price;
                break;
        }

        if (intval($this->value_in_percent) === 1) {
            $discountPrice *=  $this->value / 100;
        } else {
            $discountPrice += $this->value;
        }
        $resultPrice = $price - $discountPrice;
        return $resultPrice > 0 ? $resultPrice : 0;
    }

    static public function getTypeObjects()
    {
        $cacheKey = 'discountTypeObjects';

        if (!$result = Yii::$app->cache->get($cacheKey)) {
            $types = DiscountType::find()
                ->where(['active' => 1])
                ->orderBy(['sort_order' => SORT_ASC])
                ->all();
            foreach ($types as $type) {
                $discountTypeObject = new $type->class;
                if ($discountTypeObject instanceof AbstractDiscountType) {
                    $result[] = $discountTypeObject;
                }
            }
            Yii::$app->cache->set(
                $cacheKey,
                $result,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(
                                DiscountType::className()
                            )
                        ]
                    ]
                )
            );

        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'appliance', 'value'], 'required'],
            [['appliance'], 'string'],
            [['appliance'], 'in', 'range' => array_keys($this->applianceValues)],
            [['value', 'apply_order_price_lg'], 'number'],
            [['value_in_percent'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        foreach ($this->getTypeObjects() as $typeObject) {
            $typeObject::deleteAll(['discount_id' => $this->id]);
        }
        return parent::afterDelete();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'appliance' => Yii::t('app', 'Appliance'),
            'value' => Yii::t('app', 'Value'),
            'value_in_percent' => Yii::t('app', 'Value In Percent'),
            'apply_order_price_lg' => Yii::t('app', 'Apply Order Price Lg'),
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
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'appliance', $this->appliance]);


        return $dataProvider;
    }


    public function beforeSave($insert)
    {
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($this->className()),
                'Discount:' . $this->id . ':0'
            ]
        );

        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($this->className()),
                'Discount:' . $this->id . ':1'
            ]
        );

        return parent::beforeSave($insert);
    }
}
