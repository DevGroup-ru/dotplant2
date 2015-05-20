<?php

namespace app\modules\shop\models;

use app\modules\shop\components\SpecialPriceOrderInterface;
use app\modules\shop\components\SpecialPriceProductInterface;
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
class Discount extends \yii\db\ActiveRecord implements SpecialPriceProductInterface, SpecialPriceOrderInterface
{

    public $options = [];
    public $applianceValues = [];

    private static $_allDiscounts = [];


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

    /***
     * @param Product $product
     * @param Order $order
     * @param $price
     * @return mixed
     */
    public function getPriceProduct(Product $product, Order $order = null, $price)
    {
        self::getAllDiscounts();
        if (isset(self::$_allDiscounts['products'])) {
            foreach (self::$_allDiscounts['products'] as $discount) {
                $discountFlag = false;
                foreach (self::getTypeObjects() as $discountTypeObject) {
                    $discountFlag = $discountTypeObject
                        ->checkDiscount(
                            $discount,
                            $product,
                            $order
                        );

                    if ($discountFlag === false) {
                        break;
                    }
                }
                $special_price_list_id = SpecialPriceList::getModel(
                    self::className(),
                    $product->object->id
                )->id;

                if ($discountFlag === true) {
                    $oldPrice = $price;
                    $price = $discount->getDiscountPrice($price);
                    SpecialPriceObject::setObject(
                        $special_price_list_id,
                        $product->id,
                        ($price - $oldPrice)
                    );
                } else {
                    SpecialPriceObject::deleteAll(
                        [
                            'special_price_list_id' =>  $special_price_list_id,
                            'object_model_id' =>  $product->id
                        ]
                    );
                }
            }
        }
        return $price;
    }

    public function getPriceOrder(Order $order, $price)
    {
        self::getAllDiscounts();
        if (isset(self::$_allDiscounts['order_with_delivery'])) {
            foreach (self::$_allDiscounts['order_with_delivery'] as $discount) {
                $discountFlag = false;
                foreach (self::getTypeObjects() as $discountTypeObject) {
                    $discountFlag = $discountTypeObject
                        ->checkDiscount(
                            $discount,
                            null,
                            $order
                        );

                    if ($discountFlag === false) {
                        break;
                    }

                }

                $special_price_list_id = SpecialPriceList::getModel(
                    self::className(),
                    $order->object->id
                )->id;

                if ($discountFlag === true) {
                    $oldPrice = $price;
                    $price = $discount->getDiscountPrice($price);
                    SpecialPriceObject::setObject(
                        $special_price_list_id,
                        $order->id,
                        ($price - $oldPrice)
                    );
                } else {
                    SpecialPriceObject::deleteAll(
                        [
                           'special_price_list_id' =>  $special_price_list_id,
                            'object_model_id' =>  $order->id
                        ]
                    );
                }
            }
        }
        return $price;
    }


    public function getDescription()
    {
        return Yii::t('app', 'Discount');
    }


    public static function getAllDiscounts()
    {
        if (!self::$_allDiscounts) {
            $cacheKey = 'getAllDiscounts';
            if (!self::$_allDiscounts = Yii::$app->cache->get($cacheKey)) {
                $discounts = self::find()->all();
                foreach ($discounts as $discount) {
                    self::$_allDiscounts[$discount->appliance][] = $discount;
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    self::$_allDiscounts,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(self::className())
                            ]
                        ]
                    )
                );
            }

        }
        return self::$_allDiscounts;
    }

    public function getDiscountPrice($price)
    {
        if (intval($this->value_in_percent) === 1) {
            $price *= (100 - $this->value) / 100;
        } else {
            $price -= $this->value;
        }


        return $price;
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
}
