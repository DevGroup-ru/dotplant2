<?php

namespace app\modules\user\models;

use app\modules\shop\models\Customer;
use app\properties\HasProperties;
use app\models\PropertyGroup;
use app\models\ObjectPropertyGroup;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
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
 * @property \app\modules\user\models\UserService[] $services
 * @property string $displayName  Display name for the user visual identification
 * Relations:
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    const USER_GUEST = 0;

    public $confirmPassword;
    public $newPassword;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'except' => ['registerService']],
            ['email', 'email'],
            ['email', 'unique', 'message' => Yii::t('app', 'This email address has already been taken.')],
            ['email', 'exist', 'message' => Yii::t('app', 'There is no user with such email.'), 'on' => 'requestPasswordResetToken'],

            ['password', 'required', 'on' => ['adminSignup', 'changePassword']],
            ['password', 'string', 'min' => 8],
            [['first_name', 'last_name',], 'string', 'max' => 255],

            // change password
            [['newPassword', 'confirmPassword'], 'required'],
            [['newPassword', 'confirmPassword'], 'string', 'min' => 8],
            [['confirmPassword'], 'compare', 'compareAttribute' => 'newPassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'signup' => ['username', 'email', 'password', 'first_name', 'last_name'],
            'resetPassword' => ['password_hash', 'password_reset_token'],
            'requestPasswordResetToken' => ['email'],

            'registerService' => ['email', 'first_name', 'last_name'],
            'updateProfile' => ['email', 'first_name', 'last_name'],
            'completeRegistration' => ['first_name', 'last_name', 'username'],
            'changePassword' => ['password', 'newPassword', 'confirmPassword'],
            // admin
            'search' => ['id', 'username', 'email', 'status', 'create_time', 'first_name', 'last_name'],
            'admin' => ['username', 'status', 'email', 'password', 'first_name', 'last_name'],
            'adminSignup' => ['username', 'status', 'email', 'password', 'first_name', 'last_name', 'auth_key'],
            'passwordResetToken' => ['password_reset_token'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @return array List of all possible statuses for User instance
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_DELETED => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

        if (is_numeric($id)) {
            $model = Yii::$app->cache->get("User:$id");
            if ($model === false) {
                $model = static::find()
                    ->with('services')
                    ->where('id=:id', [':id'=>$id])
                    ->one();
                if ($model !== null) {
                    Yii::$app->cache->set(
                        "User:$id",
                        $model,
                        3600,
                        new TagDependency([
                            'tags' => [
                                ActiveRecordHelper::getObjectTag($model->className(), $model->id)
                            ]
                        ])
                    );
                }

            }
            return $model;
        } else {
            return null;
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
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return User|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (static::isPasswordResetTokenValid($token) === false) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token) === true) {
            return false;
        }
        $expire = Yii::$app->modules['user']->passwordResetTokenExpire;
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
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
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }


    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // save bindings to property groups for properties support

        $ids = $this->getPropertyIdsForUsers();

        $currentGroups = array_keys($this->getPropertyGroups());

        $newIds = array_diff($ids, $currentGroups);

        foreach ($newIds as $group_id) {
            $this->addPropertyGroup($group_id, false);
        }
        $this->updatePropertyGroupsInformation();
    }

    /**
     * Returns array of ids of propertyGroups for user model
     * @return array
     * @throws \Exception
     */
    private function getPropertyIdsForUsers()
    {
        return PropertyGroup::getDb()->cache(
            function ($db) {
                return PropertyGroup::find()
                    ->select('id')
                    ->where(['object_id' => $this->getObject()->id])
                    ->asArray()
                    ->column($db);
            },
            86400,
            new TagDependency([
                'tags' => ActiveRecordHelper::getCommonTag(PropertyGroup::className())
            ])
        );
    }


    /**
     * Returns gravatar image link for user
     * @param int $size Avatar size in pixels
     * @return string
     */
    public function gravatar($size = 40)
    {
        $hash = md5(strtolower(trim($this->email)));
        return '//www.gravatar.com/avatar/' . $hash . '?s=' . $size;
    }

    /**
     * Relation to UserService model describing social services available exact user to login
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(UserService::className(), ['user_id' => 'id']);
    }

    /**
     * @return mixed|string Display name for the user visual identification
     */
    public function getDisplayName()
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ? $name : $this->username;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->setPassword($this->password);
        }
        return true;
    }
}
