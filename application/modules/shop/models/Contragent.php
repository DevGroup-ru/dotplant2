<?php

namespace app\modules\shop\models;

use app\models\Property;
use app\models\PropertyGroup;
use app\properties\AbstractModel;
use app\properties\HasProperties;
use app\properties\PropertyValue;
use Yii;

/**
 * This is the model class for table "{{%contragent}}".
 *
 * @property integer $id
 * @property string $type
 * Relations:
 * @property Customer[] $customers
 * @property DeliveryInformation $deliveryInformation
 */
class Contragent extends \yii\db\ActiveRecord
{
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
            [['type'], 'string']
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

    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['contragent_id' => 'id']);
    }

    public function getDeliveryInformation()
    {
        return $this->hasOne(DeliveryInformation::className(), ['contragent_id' => 'id']);
    }

    public function setPropertyGroup($group)
    {
        $this->propertyGroup = $group;
    }

    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }

    public static function createEmptyContragent()
    {
        $model = new static();

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

        return $model;
    }
}
?>