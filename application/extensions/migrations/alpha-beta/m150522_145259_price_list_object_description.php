<?php

use yii\db\Schema;
use yii\db\Migration;

class m150522_145259_price_list_object_description extends Migration
{
    public function up()
    {
        $this->addColumn(
            '{{%special_price_object}}',
            'name',
            Schema::TYPE_STRING
        );
    }

    public function down()
    {
        $this->dropColumn(
            '{{%special_price_object}}',
            'name'
        );

    }

}
