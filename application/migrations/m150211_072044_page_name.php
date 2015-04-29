<?php

use app\modules\page\models\Page;
use yii\db\Migration;

class m150211_072044_page_name extends Migration
{
    public function up()
    {
        $this->addColumn(Page::tableName(), 'name', 'VARCHAR(255) DEFAULT NULL AFTER `robots`');
        $this->update(Page::tableName(), ['name' => new \yii\db\Expression('`breadcrumbs_label`')]);
    }

    public function down()
    {
        $this->dropColumn(Page::tableName(), 'name');
    }
}
