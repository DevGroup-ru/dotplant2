<?php

use yii\db\Migration;

class m150930_112734_related_products_sort extends Migration
{
    public function up()
    {
        $this->addColumn('{{%related_product}}', 'sort_order', $this->integer()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%related_product}}', 'sort_order');
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
