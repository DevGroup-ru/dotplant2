<?php

namespace app\models;

use app\properties\AbstractModel;
use app\properties\HasProperties;
use Yii;
use yii\base\NotSupportedException;
use yii\base\Security;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Class User
 * @package app\models
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property string $first_name
 * @property string $last_name
 * @property UserService[] $services
 * @property AbstractModel $abstractModel
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    public $confirmPassword;
    public $newPassword;
    public $password;
    public $profile;

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password' => Yii::t('app', 'Password'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'E-mail'),
            'status' => Yii::t('app', 'Status'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'newPassword' => Yii::t('app', 'New Password'),
            'confirmPassword' => Yii::t('app', 'Confirm Password'),
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_DELETED => Yii::t('app', 'Deleted'),
        ];
    }

    public function getAwesomeUsername()
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ? $name : $this->username;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => 'update_time',
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => HasProperties::className(),
            ],
        ];
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // see https://github.com/yiisoft/yii2/issues/2689
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        if (Yii::$app->getSession()->has('user-'.$id)) {
            return new self(Yii::$app->getSession()->get('user-'.$id));
        } else {
            if (is_numeric($id)) {
                $model = Yii::$app->cache->get("User:$id");
                if ($model === false) {
                    $model = static::findOne($id);
                    if ($model !== null) {
                        Yii::$app->cache->set("User:$id", $model, 3600);
                    }
                    
                }
                return $model;
            } else {
                return null;
            }
        }
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return null|User
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => static::STATUS_ACTIVE]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $security = new Security;
        return $security->validatePassword($password, $this->password_hash);
    }

    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'except' => ['registerService']],
            ['email', 'email'],
            ['email', 'unique', 'message' => 'This email address has already been taken.'],
            ['email', 'exist', 'message' => 'There is no user with such email.', 'on' => 'requestPasswordResetToken'],
            ['password', 'required', 'on' => ['signup', 'adminSignup', 'changePassword']],
            ['password', 'string', 'min' => 6],
            [['first_name', 'last_name',], 'string', 'max' => 255],
            // change password
            [['newPassword', 'confirmPassword'], 'required'],
            [['newPassword', 'confirmPassword'], 'string', 'min' => 6],
            [['confirmPassword'], 'compare', 'compareAttribute' => 'newPassword'],
        ];
    }

    public function scenarios()
    {
        return [
            'signup' => ['username', 'email', 'password', 'first_name', 'last_name'],
            'resetPassword' => ['password'],
            'requestPasswordResetToken' => ['email'],
            'signupEAuth' => ['username', 'email', 'password'],
            'registerService' => ['email', 'first_name', 'last_name'],
            'updateProfile' => ['email', 'first_name', 'last_name'],
            'changePassword' => ['password', 'newPassword', 'confirmPassword'],
            // admin
            'search' => ['id', 'username', 'email', 'status', 'create_time', 'first_name', 'last_name'],
            'admin' => ['username', 'status', 'email', 'password', 'first_name', 'last_name'],
            'adminSignup' => ['username', 'status', 'email', 'password', 'first_name', 'last_name'],
        ];
    }

    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $security = new Security;
        if (($insert || $this->scenario === 'resetPassword' || $this->scenario === 'admin')
            && !empty($this->password)) {
            $this->password_hash = $security->generatePasswordHash($this->password);
        }
        if ($insert) {
            $this->auth_key = $security->generateRandomKey();
        }
        return true;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            // @todo Сделать через saveProperties
            $propertyGroups = PropertyGroup::findAll(['object_id' => $this->getObject()->id]);
            foreach ($propertyGroups as $propertyGroup) {
                $objectPropertyGroup = new ObjectPropertyGroup;
                $objectPropertyGroup->attributes = [
                    'object_id' => $this->object->id,
                    'object_model_id' => $this->id,
                    'property_group_id' => $propertyGroup->id,
                ];
                $objectPropertyGroup->save();
            }
        }
    }

    /**
     * Returns gravatar image link for user
     * @param int $size
     * @return string
     */
    public function gravatar($size = 40)
    {
        $hash = md5(strtolower(trim($this->email)));
        return 'http://www.gravatar.com/avatar/' . $hash . '?s=' . $size;
    }

    public function getServices()
    {
        return $this->hasMany(UserService::className(), ['user_id' => 'id']);
    }
}
