<?php

use yii\db\Schema;
use yii\db\Migration;

class m150428_120959_page_move extends Migration
{
    public function up()
    {
        $this->update('{{%object}}', ['object_class' => 'app\modules\core\models\Page'],['name' => 'Page']);
    }

    public function down()
    {
        $this->update('{{%object}}', ['object_class' => 'app\models\Page'],['name' => 'Page']);
    }

}
