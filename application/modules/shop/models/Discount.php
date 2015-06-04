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
    const DISCOUNT_CHECKING_ORDER = 'Order';
    const DISCOUNT_CHECKING_ORDER_ITEM = 'OrderItem';

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
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @param $price
     * @param int $deliveryPrice
     * @return int
     */
    public function getDiscountPrice($price, $deliveryPrice = 0)
    {
        $discountPrice = 0;
        if (intval($this->value_in_percent) === 1) {
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

            $discountPrice *= $this->value / 100;
        } else {
            switch ($this->appliance) {
                case 'order_without_delivery':
                    $discountPrice = $this->value < $price ? $this->value : $price;
                    break;
                case 'order_with_delivery':
                    $discountPrice = $this->value < ($price + $deliveryPrice) ? $this->value : ($price + $deliveryPrice);
                    break;
                case 'delivery':
                    $discountPrice = $this->value < $deliveryPrice ? $this->value : $deliveryPrice;
                    break;
                case 'products':
                    $discountPrice = $this->value < $price ? $this->value : $price;
                    break;
            }

        }
        $resultPrice = $price - $discountPrice;
        return $resultPrice > 0 ? $resultPrice : 0;
    }

    /**
     * @param string $checkingClass
     * @return array
     */
    public static function getTypeObjects($checkingClass = null)
    {
        $checkingClass = (in_array($checkingClass, [static::DISCOUNT_CHECKING_ORDER, static::DISCOUNT_CHECKING_ORDER_ITEM]) ? $checkingClass : null);
        $cacheKey = 'Discount_TypeObjects'.$checkingClass;

        if (false === $result = Yii::$app->cache->get($cacheKey)) {
            $types = DiscountType::find()
                ->select(['class'])
                ->where(['active' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);

            if (null !== $checkingClass) {
                $types->andWhere(['checking_class' => $checkingClass]);
            }

            $result = array_reduce($types->asArray()->all(),
                function ($result, $item)
                {
                    $className = $item['class'];
                    if (is_subclass_of($className, '\app\modules\shop\models\AbstractDiscountType')) {
                        $result[] = new $className();
                    }
                    return $result;
                }, []);

            if (empty($result)) {
                return $result;
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
    public function afterDelete()
    {
        foreach ($this->getTypeObjects() as $typeObject) {
            $typeObject::deleteAll(['discount_id' => $this->id]);
        }
        return parent::afterDelete();
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = static::find();
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
