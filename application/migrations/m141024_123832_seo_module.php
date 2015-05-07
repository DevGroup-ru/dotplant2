<?php

use yii\db\Migration;

class m141024_123832_seo_module extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            \app\modules\seo\models\Config::tableName(),
            [
                'key' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'value' => 'TEXT NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Counter::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) DEFAULT NULL',
                'description' => 'TEXT DEFAULT NULL',
                'code' => 'TEXT NOT NULL ',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\LinkAnchor::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'model_name' => 'VARCHAR(60) NOT NULL',
                'model_id' => 'INT UNSIGNED NOT NULL',
                'anchor' => 'TEXT NOT NULL',
                'sort_order' =>  'INT DEFAULT \'0\'',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\LinkAnchorBinding::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'link_anchor_id' => 'INT UNSIGNED NOT NULL',
                'view_file' => 'VARCHAR(255) NOT NULL',
                'params_hash' => 'VARCHAR(255) DEFAULT NULL',
                'model_name' => 'VARCHAR(255) NOT NULL',
                'model_id' => 'VARCHAR(255) NOT NULL',
                'KEY `ix-link_anchor_id` (`link_anchor_id`)',
                'KEY `model_name` (`model_name`, `model_id`)',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\ModelAnchorIndex::tableName(),
            [
                'model_name' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'model_id' => 'VARCHAR(255) NOT NULL',
                'next_index' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Redirect::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'type' => 'enum(\'STATIC\',\'PREG\') NOT NULL DEFAULT \'STATIC\'',
                'from' => 'VARCHAR(255) NOT NULL',
                'to' => 'VARCHAR(255) NOT NULL',
                'active' => 'TINYINT NOT NULL DEFAULT \'1\'',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Meta::tableName(),
            [
                'key' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'name' => 'VARCHAR(255) NOT NULL',
                'content' => 'VARCHAR(255) NOT NULL ',
            ],
            $tableOptions
        );
        $this->createTable(
            \app\modules\seo\models\Sitemap::tableName(),
            [
                'uid' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
                'url' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable(\app\modules\seo\models\Sitemap::tableName());
        $this->dropTable(\app\modules\seo\models\Meta::tableName());
        $this->dropTable(\app\modules\seo\models\Redirect::tableName());
        $this->dropTable(\app\modules\seo\models\ModelAnchorIndex::tableName());
        $this->dropTable(\app\modules\seo\models\LinkAnchorBinding::tableName());
        $this->dropTable(\app\modules\seo\models\LinkAnchor::tableName());
        $this->dropTable(\app\modules\seo\models\Counter::tableName());
        $this->dropTable(\app\modules\seo\models\Config::tableName());
    }
}
