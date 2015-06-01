<?php

use yii\db\Schema;
use yii\db\Migration;

class m141211_111556_default_view_fix extends Migration
{
    public function up()
    {
        $default_view = \app\models\View::findOne(['id' => 1]);
        $default_view->view = 'default';
        $default_view->save();
    }

    public function down()
    {
        $default_view = \app\models\View::findOne(['id' => 1]);
        $default_view->view = 'show';
        $default_view->save();
    }
}
