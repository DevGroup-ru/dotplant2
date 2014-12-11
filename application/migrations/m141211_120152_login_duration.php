<?php

use app\models\Config;
use yii\db\Migration;

class m141211_120152_login_duration extends Migration
{
    public function up()
    {
        $core = Config::findOne(['path' => 'core']);
        if (!is_null($core)) {
            $config = new Config;
            $config->attributes = [
                'parent_id' => $core->id,
                'name' => 'Login session duration',
                'key' => 'loginSessionDuration',
                'value' => (string) (60 * 60 * 24 * 30),
                'preload' => 1,
            ];
            $config->save();
        }
    }

    public function down()
    {
        Config::deleteAll(['path' => 'core.loginSessionDuration']);
    }
}
