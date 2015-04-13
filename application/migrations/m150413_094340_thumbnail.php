<?php

use yii\db\Migration;

class m150413_094340_thumbnail extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%thumbnail}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
                'img_id' => 'INT UNSIGNED NOT NULL',
                'thumb_src' => 'VARCHAR(255) NOT NULL',
                'size_id' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%thumbnail_size}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
                'width' => 'INT UNSIGNED NOT NULL',
                'height' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%watermark}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
                'watermark_src' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%thumbnail_watermark}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
                'thumb_id' => 'INT UNSIGNED NOT NULL',
                'water_id' => 'INT UNSIGNED NOT NULL',
                'src' => 'VARCHAR(255) NOT NULL',
                //@todo add type mb enum?
            ],
            $tableOptions
        );
        //@todo drop thumb column and create thumb in table, insert default size 80x80
    }

    public function down()
    {
        $this->dropTable('{{%thumbnail}}');
        $this->dropTable('{{%thumbnail_size}}');
        $this->dropTable('{{%watermark}}');
        $this->dropTable('{{%thumbnail_watermark}}');
    }
}
