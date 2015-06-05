<?php

use yii\db\Schema;
use yii\db\Migration;

class m150205_100004_emailConf_fix extends Migration
{
    public function up()
    {
        /**
         * @TODO: All configs into module
         */
//        $this->update(Config::tableName(), ['key' => 'emailHost', 'path' => 'core.emailConfig.emailHost', 'name' => 'host'], ['key' => 'host']);
//        $this->update(Config::tableName(), ['key' => 'emailPort', 'path' => 'core.emailConfig.emailPort', 'name' => 'port'], ['key' => 'port']);
//        $this->update(Config::tableName(), ['key' => 'emailEncryption','path' => 'core.emailConfig.emailEncryption', 'name' => 'encryption'], ['key' => 'encryption']);
//        $this->update(Config::tableName(), ['key' => 'emailUsername', 'path' => 'core.emailConfig.emailUsername', 'name' => 'username'], ['key' => 'username']);
//        $this->update(Config::tableName(), ['key' => 'emailPassword', 'path' => 'core.emailConfig.emailPassword', 'name' => 'password'], ['key' => 'password']);
//
//        $conf = Config::find()->where(['key' => 'emailConfig'])->one();
//        $this->insert(Config::tableName(),
//            [
//                'parent_id' => $conf->id,
//                'name' => 'Transport',
//                'key' => 'transport',
//                'value' => 'Swift_SmtpTransport',
//                'path' => 'emailConfig.transport',
//            ]
//        );
//        $this->update(Config::tableName(), ['name' => 'transport', 'path' => 'core.emailConfig.emailTransport', 'key' => 'emailTransport'], ['key' => 'transport']);
    }

    public function down()
    {
        return true;
    }
}
