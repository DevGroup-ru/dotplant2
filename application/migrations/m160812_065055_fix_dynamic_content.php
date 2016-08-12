<?php

use app\models\DynamicContent;
use yii\db\Migration;

class m160812_065055_fix_dynamic_content extends Migration
{
    public function up()
    {
        $this->alterColumn(
            DynamicContent::tableName(),
            'apply_if_last_category_id',
            'INT DEFAULT NULL'
        );
    }

    public function down()
    {
        $this->alterColumn(
            DynamicContent::tableName(),
            'apply_if_last_category_id',
            'INT UNSIGNED DEFAULT NULL'
        );
    }
}
