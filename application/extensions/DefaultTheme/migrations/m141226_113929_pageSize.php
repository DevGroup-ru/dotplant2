<?php

use app\models\Config;
use yii\db\Migration;

class m141226_113929_pageSize extends Migration
{
    public function up()
    {
        $this->insert(
            Config::tableName(),
            [
                'name' => 'Page',
                'key' => 'page',
                'value' => '',
                'preload' => 1,
                'path' => 'page',
            ]
        );
        $id = Yii::$app->db->lastInsertID;
        $this->batchInsert(
            Config::tableName(),
            ['parent_id', 'name', 'key', 'value', 'preload', 'path'],
            [
                [$id, 'Min pages per list', 'minPagesPerList', 1, 1, 'page.minPagesPerList'],
                [$id, 'Max pages per list', 'maxPagesPerList', 50, 1, 'page.maxPagesPerList'],
                [$id, 'Pages per list', 'pagesPerList', 10, 1, 'page.pagesPerList'],
            ]
        );
    }

    public function down()
    {
        $this->delete(Config::tableName(), "path LIKE 'page.%'");
    }
}
