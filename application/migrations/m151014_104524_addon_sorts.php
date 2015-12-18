<?php

use yii\db\Migration;

class m151014_104524_addon_sorts extends Migration
{
    public function up()
    {
        $this->addColumn('{{%addon}}', 'sort_order', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%addon_category}}', 'sort_order', $this->integer()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%addon}}', 'sort_order');
        $this->dropColumn('{{%addon_category}}', 'sort_order');
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
