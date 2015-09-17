<?php

namespace app\modules\shop\models;

use app\models\Property;
use app\models\PropertyGroup;
use app\properties\AbstractModel;
use app\properties\HasProperties;
use app\properties\PropertyValue;
use app\properties\traits\PropertyTrait;
use Yii;

/**
 * This is the model class for table "{{%order_delivery_information}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $shipping_option_id
 * @property double $shipping_price
 * @property double $shipping_price_total
 * @property string $planned_delivery_date
 * @property string $planned_delivery_time
 * @property string $planned_delivery_time_range
 * Relations:
 * @property Order $order
 * @property ShippingOption $shippingOption
 */
class OrderDeliveryInformation extends \yii\db\ActiveRecord
{
    use PropertyTrait;

    /** @var PropertyGroup $propertyGroup */
    protected $propertyGroup = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_delivery_information}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'shipping_option_id'], 'required'],
            [['order_id', 'shipping_option_id'], 'integer'],
            [['planned_delivery_date', 'planned_delivery_time'], 'safe'],
            [['planned_delivery_time_range'], 'string', 'max' => 255],
            [['shipping_price', 'shipping_price_total'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'shipping_option_id' => Yii::t('app', 'Shipping option ID'),
            'shipping_price' => Yii::t('app', 'Shipping price'),
            'shipping_price_total' => Yii::t('app', 'Shipping price total'),
            'planned_delivery_date' => Yii::t('app', 'Planned Delivery Date'),
            'planned_delivery_time' => Yii::t('app', 'Planned Delivery Time'),
            'planned_delivery_time_range' => Yii::t('app', 'Planned Delivery Time Range'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['readonly'] = [];
        $scenarios['shipping_option_select'] = ['shipping_option_id'];
        return $scenarios;
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return ShippingOption|null
     */
    public function getShippingOption()
    {
        return $this->hasOne(ShippingOption::className(), ['id' => 'shipping_option_id']);
    }

    /**
     * @param PropertyGroup $group
     */
    public function setPropertyGroup(PropertyGroup $group)
    {
        $this->propertyGroup = $group;
    }

    /**
     * @return PropertyGroup
     */
    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }

    /**
     * @param integer|null $id
     * @return OrderDeliveryInformation|null
     */
    public static function getByOrderId($id = null)
    {
        return static::findOne(['order_id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        /** @var Order $order */
        if (null !== $order = $this->order) {
            $order->calculate(true);
        }
    }

    /**
     * @param Order $order
     * @param boolean $dummyObject Empty object, non saved to database
     * @return OrderDeliveryInformation|null
     * @throws \Exception
     */
    public static function createNewOrderDeliveryInformation(Order $order, $dummyObject = true)
    {
        /** @var OrderDeliveryInformation|HasProperties $model */
        $model = new static();
        $model->order_id = $order->id;
        $model->loadDefaultValues();

        $groups = PropertyGroup::getForObjectId($model->getObject()->id, true);
        $group = array_shift($groups);

        if (null !== $group) {
            $model->setPropertyGroup($group);
            $abstractModel = new AbstractModel();
            $abstractModel->setPropertiesModels(array_reduce($group->properties,
                function($result, $item)
                {
                    /** @var Property $item */
                    $result[$item->key] = $item;
                    return $result;
                }, []));
            $abstractModel->setAttributes(array_reduce($group->properties,
                function($result, $item) use ($model)
                {
                    /** @var Property $item */
                    $result[$item->key] = new PropertyValue([], $item->id, $model->getObject()->id, null);
                    return $result;
                }, []));
            $abstractModel->setFormName('OrderDeliveryInformationNew');
            $model->setAbstractModel($abstractModel);
        }

        if (!$dummyObject) {
            $model->shipping_option_id = 0;
            if ($model->save()) {
                if (!empty($model->getPropertyGroup())) {
                    $model->getPropertyGroup()->appendToObjectModel($model);
                }
            } else {
                return null;
            }
        }

        return $model;
    }
}
?>