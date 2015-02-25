<?php

use yii\db\Schema;
use yii\db\Migration;

class m150218_091236_currency extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable(
            '{{%currency_rate_provider}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'class_name' => Schema::TYPE_STRING,
                'params' => Schema::TYPE_TEXT,
            ],
            $tableOptions
        );

        $this->insert(
            '{{%currency_rate_provider}}',
            [
                'name' => 'Google Finance',
                'class_name' => 'Swap\\Provider\\GoogleFinanceProvider',
            ]
        );

        //! @todo add another available currency rate providers


        $this->createTable(
            '{{%currency}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'iso_code' => Schema::TYPE_STRING,
                'is_main' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'convert_nominal' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 1',
                'convert_rate' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 1',
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'intl_formatting' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'min_fraction_digits' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'max_fraction_digits' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 2',
                'dec_point' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'.\'',
                'thousands_sep' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \' \'',
                'format_string' => Schema::TYPE_STRING,
                'additional_rate' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'additional_nominal' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0',
                'currency_rate_provider_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );

        $this->insert('{{%currency}}', [
            'name' => 'Ruble',
            'iso_code' => 'RUB',
            'is_main' => 1,
            'format_string' => '# руб.',
        ]);
        $this->insert('{{%currency}}', [
            'name' => 'US Dollar',
            'iso_code' => 'USD',
            'convert_nominal' => 1,
            'convert_rate' => 62.8353,
            'sort_order' => 1,
            'format_string' => '$ #',
            'thousands_sep' => '.',
            'dec_point' => ',',
        ]);
        $this->insert('{{%currency}}', [
            'name' => 'Euro',
            'iso_code' => 'EUR',
            'convert_rate' => 71.3243,
            'format_string' => '&euro; #',
        ]);

        $this->addColumn('{{%product}}', 'currency_id', Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1');

        $this->insert(
            '{{%backgroundtasks_task}}',
            [
                'action' => 'currency/update',
                'type' => 'REPEAT',
                'initiator' => 1,
                'name' => 'Currency update',
                'cron_expression' => '0 0 * * *',
            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%currency}}');
        $this->dropColumn('{{%product}}', 'currency_id');
    }
}
