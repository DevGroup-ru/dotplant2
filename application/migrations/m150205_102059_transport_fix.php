<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Config;

class m150205_102059_transport_fix extends Migration
{
    public function up()
    {
        $this->update(Config::tableName(), ['name' => 'transport', 'path' => 'core.emailConfig.emailTransport', 'key' => 'emailTransport'], ['key' => 'transport']);
    }

    public function down()
    {
        $this->update(Config::tableName(), ['key' => 'transport'], ['key' => 'emailTransport']);

        return true;
    }
}
