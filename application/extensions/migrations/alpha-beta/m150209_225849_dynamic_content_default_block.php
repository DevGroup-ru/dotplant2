<?php

use yii\db\Schema;
use yii\db\Migration;

class m150209_225849_dynamic_content_default_block extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%dynamic_content}}', 'content_block_name', 'VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT \'content\'');
    }

    public function down()
    {
        $this->alterColumn('{{%dynamic_content}}', 'content_block_name', 'VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT \'bottom-text\'');
    }
}
