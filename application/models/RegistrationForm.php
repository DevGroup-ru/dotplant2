<?php

namespace app\models;

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
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'confirmPassword'], 'required'],
            ['email', 'email', 'checkDNS' => true],
            ['password', 'string', 'min' => 6],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'E-mail'),
            'password' => Yii::t('app', 'Password'),
            'confirmPassword' => Yii::t('app', 'Confirm Password'),
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = $this->getUser();
        if ($user !== null) {
            $this->addError('username', 'Choose other name');
            return false;
        }
        $user = new User(['scenario' => 'signup']);
        $user->setAttributes(
            [
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
            ]
        );
        return $user->save() && Yii::$app->user->login($user, 0);
    }

    public function signupService($serviceType, $serviceId)
    {
        if (!$this->validate()) {
            return false;
        }
        $user = $this->getUser();
        if ($user !== null) {
            $this->addError('username', 'Choose other name');
            return false;
        }
        $user = new User(['scenario' => 'signup']);
        $user->setAttributes(
            [
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
            ]
        );
        if (!$user->save()) {
            return false;
        }
        $userService = new UserService;
        $userService->setAttributes(
            [
                'user_id' => $user->id,
                'service_type' => $serviceType,
                'service_id' => $serviceId,
            ]
        );
        $userService->save();
        Yii::$app->user->login($user, 0);
        return true;
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
