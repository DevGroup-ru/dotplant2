<?php

namespace app\modules\user\models;

use Yii;
use yii\base\Model;

/**
 * RegistrationForm is the model behind the login form.
 */
class RegistrationForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $confirmPassword;

    private $user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'confirmPassword'], 'required'],
            ['email', 'email', 'checkDNS' => true],
            ['password', 'string', 'min' => 8],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'E-mail'),
            'password' => Yii::t('app', 'Password'),
            'confirmPassword' => Yii::t('app', 'Confirm Password'),
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate() === true) {
            $user = new User;
            $user->setScenario('signup');
            $user->username = $this->username;
            $user->password = $this->password;
            $user->email = $this->email;
            $user->generateAuthKey();
            if ($user->save() === false) {
                $this->addErrors($user->errors);
                return null;
            }
            return $user;
        }

        return null;

    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    private function getUser()
    {
        if ($this->user === false) {
            $this->user = User::findByUsername($this->username);
        }
        return $this->user;
    }
}
