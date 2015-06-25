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
 * This is the model class for table "{{%contragent}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property string $type
 * Relations:
 * @property Customer $customer
 * @property DeliveryInformation $deliveryInformation
 * @property Order[] $orders
 */
class Contragent extends \yii\db\ActiveRecord
{
    use PropertyTrait;

    /** @var PropertyGroup $propertyGroup */
    protected $propertyGroup = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contragent}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id'], 'integer'],
            [['type'], 'string'],
            [['customer_id', 'type'], 'required'],
            [['type'], 'default', 'value' => 'Individual'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
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
        $scenarios['search'] = [
            'type',
        ];
        return $scenarios;
    }

    /**
     * @return Order[]|null
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['contragent_id' => 'id']);
    }

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return DeliveryInformation|null
     */
    public function getDeliveryInformation()
    {
        return $this->hasOne(DeliveryInformation::className(), ['contragent_id' => 'id']);
    }

    /**
     * @param PropertyGroup $group
     */
    public function setPropertyGroup(PropertyGroup $group)
    {
        $this->propertyGroup = $group;
    }

    /**
     * @return PropertyGroup|null
     */
    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }

    /**
     * @param Customer $customer
     * @param bool $dummyObject
     * @return Contragent|null
     */
    public static function createEmptyContragent(Customer $customer, $dummyObject = true)
    {
        /** @var Contragent|HasProperties $model */
        $model = new static();
        $model->customer_id = $customer->id;
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
            $abstractModel->setFormName('ContragentNew');
            $model->setAbstractModel($abstractModel);
        }

        if (!$dummyObject) {
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