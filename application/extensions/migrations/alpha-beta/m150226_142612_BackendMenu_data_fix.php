<?php

use app\backend\models\BackendMenu;
use yii\db\Migration;

class m150226_142612_BackendMenu_data_fix extends Migration
{
    public function up()
    {
        $data = BackendMenu::findOne(['name' => 'Data']);
        $data->route = 'data/file/index';
        $data->save();
    }

    public function down()
    {
        $data = BackendMenu::findOne(['name' => 'Data']);
        $data->route = '/data/file/index';
        $data->save();
    }
}
