<?php

use yii\db\Schema;
use yii\db\Migration;

class m141121_094906_data extends Migration
{
    public function up()
    {
        $this->createTable(
            'data_import',
            [
                'user_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'object_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'filename' => Schema::TYPE_STRING . ' DEFAULT NULL',
                'status' => 'enum(\'complete\',\'failed\',\'process\') NOT NULL DEFAULT \'process\'',
                'update_time' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'PRIMARY KEY (`user_id`,`object_id`)',
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $this->createTable(
            'data_export',
            [
                'user_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'object_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'filename' => Schema::TYPE_STRING . ' DEFAULT NULL',
                'status' => 'enum(\'complete\',\'failed\',\'process\') NOT NULL DEFAULT \'process\'',
                'update_time' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
                'PRIMARY KEY (`user_id`,`object_id`)',
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );
    }

    public function down()
    {
        $this->dropTable('data_import');
        $this->dropTable('data_export');
    }
}
