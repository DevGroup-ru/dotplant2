<?php

use yii\db\Schema;
use yii\db\Migration;

class m150403_101144_config_value_type_change extends Migration
{
    public function up()
    {
        $this->alterColumn(
            '{{%config}}',
            'value',
            Schema::TYPE_TEXT
        );
    }

    public function down()
    {
        $this->alterColumn(
            '{{%config}}',
            'value',
            Schema::TYPE_STRING
        );

        return true;
    }
}
?>