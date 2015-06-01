<?php

use yii\db\Schema;
use yii\db\Migration;

class m150512_140932_extensions_remove_packagist_name extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%extensions}}', 'packagist_name');
    }

    public function down()
    {
        echo "m150512_140932_extensions_remove_packagist_name cannot be reverted. We are so sorry :-(\n";

        return false;
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
