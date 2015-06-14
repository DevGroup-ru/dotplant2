<?php

use yii\db\Schema;
use yii\db\Migration;

class m150614_153629_larger_image_description extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%image}}', 'image_description', Schema::TYPE_TEXT);
    }

    public function down()
    {
        $this->alterColumn('{{%image}}', 'image_description', Schema::TYPE_STRING);
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
