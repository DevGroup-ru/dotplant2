<?php

namespace app\modules\installer\models;

use Yii;

class AdminUser extends \yii\base\Model
{
    public $username = 'admin';
    public $password = '';
    public $email = 'noreply@dotplant.ru';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['email'],
                'email'
            ],
            [
                [
                    'email',
                    'username',
                    'password',
                ],
                'filter',
                'filter' => 'trim',
            ],
            [
                [
                    'username',
                    'password',
                ],
                'required',
            ],
            ['password', 'string', 'min' => 8],
        ];
    }
}