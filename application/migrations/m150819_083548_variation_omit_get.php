<?php

use yii\db\Schema;
use yii\db\Migration;

class m150819_083548_variation_omit_get extends Migration
{

    public function up()
    {
        $this->addColumn('{{%theme_variation}}', 'omit_get_params', Schema::TYPE_BOOLEAN . ' DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%theme_variation}}', 'omit_get_params');
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
