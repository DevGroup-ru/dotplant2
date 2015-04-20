<?php

use yii\db\Schema;
use yii\db\Migration;

class m150414_123604_ContentBlock extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%content_block}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'key' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'value' => Schema::TYPE_TEXT,
                'preload' => 'TINYINT DEFAULT \'0\'',
            ]
        );
    }
    public function down()
    {
        $this->dropTable('{{%content_block}}');
    }
}
