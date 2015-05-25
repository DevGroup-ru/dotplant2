<?php

namespace app\modules\shop\models;

use app\models\Property;
use app\models\PropertyGroup;
use app\properties\AbstractModel;
use app\properties\HasProperties;
use app\properties\PropertyValue;
use Yii;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * Relations:
 * @property Contragent[] $contragents
 * @property Contragent $contragent
 */
class Customer extends \yii\db\ActiveRecord
{
    protected $propertyGroup = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['user_id', 'first_name'], 'required'],
            [['first_name', 'middle_name', 'last_name', 'email', 'phone'], 'string', 'max' => 255],
            [['email'], 'email'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'first_name' => Yii::t('app', 'First Name'),
            'middle_name' => Yii::t('app', 'Middle Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'email' => Yii::t('app', 'Email'),
            'phone' => Yii::t('app', 'Phone'),
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

    public function getContragents()
    {
        return $this->hasMany(Contragent::className(), ['customer_id' => 'id']);
    }

    public function getContragent()
    {
        return $this->hasOne(Contragent::className(), ['customer_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public function getContragentById($id = null)
    {
        if (empty($id)) {
            return null;
        }

        return Contragent::findOne(['customer_id' => $this->id, 'id' => $id]);
    }

    /**
     * @param null $id
     * @return Customer|null
     */
    public static function getCustomerByUserId($id = null)
    {
        return intval($id) > 0 ? static::findOne(['user_id' => $id]) : null;
    }

    public static function createEmptyCustomer($user_id = 0)
    {
        $model = new static();
        $model->user_id = $user_id;
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
            $abstractModel->setFormName('CustomerNew');
            $model->setAbstractModel($abstractModel);
        }

        return $model;
    }

    public function setPropertyGroup($group)
    {
        $this->propertyGroup = $group;
    }

    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }
}
?>