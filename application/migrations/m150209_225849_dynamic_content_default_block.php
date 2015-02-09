<?php

use yii\db\Schema;
use yii\db\Migration;

class m150209_225849_dynamic_content_default_block extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `dynamic_content` CHANGE `content_block_name` `content_block_name` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'content';");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `dynamic_content` CHANGE `content_block_name` `content_block_name` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'bottom-text';");
    }
}
