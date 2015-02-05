<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Config;

class m150205_100004_emailConf_fix extends Migration
{
    public function up()
    {
        $this->update(Config::tableName(), ['key' => 'emailHost', 'path' => 'core.emailConfig.emailHost', 'name' => 'host'], ['key' => 'host']);
        $this->update(Config::tableName(), ['key' => 'emailPort', 'path' => 'core.emailConfig.emailPort', 'name' => 'port'], ['key' => 'port']);
        $this->update(Config::tableName(), ['key' => 'emailEncryption','path' => 'core.emailConfig.emailEncryption', 'name' => 'encryption'], ['key' => 'encryption']);
        $this->update(Config::tableName(), ['key' => 'emailUsername', 'path' => 'core.emailConfig.emailUsername', 'name' => 'username'], ['key' => 'username']);
        $this->update(Config::tableName(), ['key' => 'emailPassword', 'path' => 'core.emailConfig.emailPassword', 'name' => 'password'], ['key' => 'password']);
    }

    public function down()
    {
        $this->update(Config::tableName(), ['key' => 'host', 'path' => 'core.emailConfig.host'], ['key' => 'emailHost']);
        $this->update(Config::tableName(), ['key' => 'port', 'path' => 'core.emailConfig.port'], ['key' => 'emailPort']);
        $this->update(Config::tableName(), ['key' => 'encryption', 'path' => 'core.emailConfig.encryption'], ['key' => 'emailEncryption']);
        $this->update(Config::tableName(), ['key' => 'username', 'path' => 'core.emailConfig.username'], ['key' => 'emailUsername']);
        $this->update(Config::tableName(), ['key' => 'password','path' => 'core.emailConfig.password'], ['key' => 'emailPassword']);

        return true;
    }
}
