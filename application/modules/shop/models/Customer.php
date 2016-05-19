<?php

namespace app\modules\shop\models;

use app\models\Property;
use app\models\PropertyGroup;
use app\modules\user\models\User;
use app\properties\AbstractModel;
use app\properties\HasProperties;
use app\properties\PropertyValue;
use app\properties\traits\PropertyTrait;
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
 * @property Order[] $orders
 * @property User $user
 */
class Customer extends \yii\db\ActiveRecord
{
    use PropertyTrait;

    protected static $mapUsers = [];
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
            [['email', 'phone'], 'required', 'on' => 'registerUser'],
            [['email'], 'unique', 'targetClass' => User::className(), 'on' => 'registerUser']
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

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['readonly'] = [];
        $scenarios['search'] = [
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
        ];
        return $scenarios;
    }

    /**
     * @return Order[]|null
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return Contragent[]|null
     */
    public function getContragents()
    {
        return $this->hasMany(Contragent::className(), ['customer_id' => 'id']);
    }

    /**
     * @return Contragent|null
     */
    public function getContragent()
    {
        return $this->hasOne(Contragent::className(), ['customer_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @param integer|null $id
     * @return Contragent|null
     */
    public function getContragentById($id = null)
    {
        if (empty($id)) {
            return null;
        }

        return Contragent::findOne(['customer_id' => $this->id, 'id' => $id]);
    }

    /**
     * @param integer $id
     * @return Customer|null
     */
    public static function getCustomerByUserId($id = 0)
    {
        if (intval($id) <= 0) {
            return null;
        } else {
            if (!isset(static::$mapUsers[$id])) {
                static::$mapUsers[$id] = static::findOne(['user_id' => $id]);
            }
            return static::$mapUsers[$id];
        }
    }

    /**
     * @param int $user_id
     * @param bool $dummyObject
     * @return Customer|null
     */
    public static function createEmptyCustomer($user_id = 0, $dummyObject = true)
    {
        $model = new static();
        $model->user_id = intval($user_id);
        $model->loadDefaultValues();

        if ($model->user_id === 0 && (int)Yii::$app->getModule('shop')->registrationGuestUserInCart === 1) {
            $model->setScenario('registerUser');
        }

        $groups = PropertyGroup::getForObjectId($model->getObject()->id, true);
        $group = array_shift($groups);

        if (null !== $group) {
            $model->setPropertyGroup($group);
            $abstractModel = new AbstractModel();
            $abstractModel->setPropertiesModels(array_reduce($group->properties,
                function ($result, $item) {
                    /** @var Property $item */
                    $result[$item->key] = $item;
                    return $result;
                }, []));
            $abstractModel->setAttributes(array_reduce($group->properties,
                function ($result, $item) use ($model) {
                    /** @var Property $item */
                    $result[$item->key] = new PropertyValue([], $item->id, $model->getObject()->id, null);
                    return $result;
                }, []));
            $abstractModel->setFormName('CustomerNew');
            $model->setAbstractModel($abstractModel);
        }

        if (!$dummyObject) {
            $model->first_name = 'Имя';
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

    public function beforeSave($insert)
    {
        if ($this->scenario == 'registerUser') {
            $password = Yii::$app->security->generateRandomString(10);
            $user = new User();
            $user->setScenario('signup');
            $user->username = $this->email;
            $user->email = $this->email;
            $user->first_name = $this->first_name;
            $user->last_name = $this->last_name;
            $user->password = $password;
            $user->generateAuthKey();
            if ($user->save()) {

                    Yii::$app->mail->compose('new-user-in-order', ['user' => $user, 'password' => $password])
                        ->setFrom(Yii::$app->getModule('core')->emailConfig['mailFrom'])
                        ->setTo($this->email)
                        ->setSubject(
                            Yii::t(
                                'app',
                                'Welcome to {appName}',
                                [
                                    'appName' => Yii::$app->getModule('DefaultTheme')->siteName
                                ]

                            )
                        )
                        ->send();
                if (Yii::$app->user->isGuest){
                    Yii::$app->user->login($user, 86400);
                }
                $this->user_id = $user->id;
            }


        }

        return parent::beforeSave($insert);
    }
}

?>