<?php

use yii\db\Migration;

class m141209_151057_subdomain extends Migration
{
    public function up()
    {
        $serverName = '';
        if (getenv("SERVER_NAME")) {
            $serverName = getenv("SERVER_NAME");
        } else {
            $stdIn = fopen("php://stdin", "r");
            echo "\nEnter server name (ie. localhost): ";
            $serverName = trim(fgets($stdIn));
            if (empty($serverName)) {
                $serverName = 'localhost';
            }
            fclose($stdIn);
        }
        $core = \app\models\Config::find()->where(['name' => 'Core'])->one();
        $this->insert(\app\models\Config::tableName(), [
            'parent_id' => $core->id,
            'name' => 'Server name',
            'key' => 'serverName',
            'value' => $serverName,
            'preload' => 1,
            'path' => $core->path . '.serverName',
        ]);
    }

    public function down()
    {
        $this->delete(\app\models\Config::tableName(), ['key' => 'serverName']);
    }
}
