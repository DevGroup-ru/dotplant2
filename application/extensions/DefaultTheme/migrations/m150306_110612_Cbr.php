<?php

use yii\db\Migration;

class m150306_110612_Cbr extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%currency_rate_provider}}',
            [
                'name' => 'Cbr Finance',
                'class_name' => 'app\\components\\swap\\provider\\CbrFinanceProvider',
            ]
        );
    }

    public function down()
    {
        $this->delete('{{%currency_rate_provider}}', ['name' => 'Cbr Finance']);
    }
}
