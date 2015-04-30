<?php

use yii\db\Schema;
use yii\db\Migration;

class m150424_143926_shop_module extends Migration
{
    public function up()
    {
        $this->insert('{{%configurable}}', [
            'module' => 'shop',
            'sort_order' => 4,
            'section_name' => 'Shop',
        ]);
    }

    public function down()
    {
        $this->delete('{{%configurable}}', 'module=:module', [':module'=>'shop']);
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
