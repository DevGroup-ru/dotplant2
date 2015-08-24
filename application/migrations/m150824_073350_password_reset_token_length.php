<?php

use app\modules\user\models\User;
use yii\db\Migration;

class m150824_073350_password_reset_token_length extends Migration
{
    public function up()
    {
        $this->alterColumn(
            User::tableName(),
            'password_reset_token',
            'BINARY(43)'
        );
    }

    public function down()
    {
        $this->alterColumn(
            User::tableName(),
            'password_reset_token',
            'VARBINARY(32)'
        );
    }
}
