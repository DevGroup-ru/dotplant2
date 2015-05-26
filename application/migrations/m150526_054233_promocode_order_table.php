<?php

use yii\db\Schema;
use yii\db\Migration;

class m150526_054233_promocode_order_table extends Migration
{
    public function up()
    {

        $this->alterColumn(
            '{{%discount_code}}',
            'valid_from',
            Schema::TYPE_TIMESTAMP . ' NULL DEFAULT NULL'
        );

        $this->alterColumn(
            '{{%discount_code}}',
            'valid_till',
            Schema::TYPE_TIMESTAMP . ' NULL DEFAULT NULL'
        );

        $this->alterColumn(
            '{{%discount_code}}',
            'maximum_uses',
            Schema::TYPE_INTEGER . ' DEFAULT NULL'
        );

        $this->dropColumn(
            '{{%discount_code}}',
            'used'
        );


        $this->createTable(
            '{{%order_code}}',
            [
                'id' => Schema::TYPE_PK,
                'order_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'discount_code_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'status' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0'
            ]
        );

    }

    public function down()
    {
        $this->dropTable('{{%order_code}}');

        $this->addColumn(
            '{{%discount_code}}',
            'used',
            Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0'
        );


    }

}
