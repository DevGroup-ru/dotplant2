<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Config;

class m150203_111737_mail_transport_field extends Migration
{
    public function up()
    {
        $conf = Config::find()->where(['key' => 'emailConfig'])->one();
        $this->insert(Config::tableName(),
            [
                'parent_id' => $conf->id,
                'name' => 'Transport',
                'key' => 'transport',
                'value' => 'Swift_SmtpTransport',
                'path' => 'emailConfig.transport',
            ]
        );
    }

    public function down()
    {
        $this->delete(Config::tableName(),
            [
                'key' => 'transport',
            ]
        );

        return true;
    }
}
