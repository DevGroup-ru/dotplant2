<?php

use app\models\DynamicContent;
use yii\db\Migration;

class m150629_104621_dynamic_content_announce extends Migration
{
    public function up()
    {
        $this->addColumn(DynamicContent::tableName(), 'announce', 'TEXT NULL DEFAULT NULL AFTER `content_block_name`');
    }

    public function down()
    {
        $this->dropColumn(DynamicContent::tableName(), 'announce');
    }
}
