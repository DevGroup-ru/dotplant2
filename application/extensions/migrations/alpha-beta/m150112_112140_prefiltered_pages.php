<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\backend\models\BackendMenu;

class m150112_112140_prefiltered_pages extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%prefiltered_pages}}',
            [
                'id' => Schema::TYPE_PK,
                'slug' => Schema::TYPE_STRING,

                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',

                'last_category_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
                'params' => 'TEXT',

                'title' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'announce' =>  'TEXT',
                'content' =>  'TEXT',
                'h1' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'meta_description' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',
                'breadcrumbs_label' => Schema::TYPE_STRING . ' NOT NULL DEFAULT \'\'',

                'view_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $prefiltered_pages_item = new BackendMenu();
        $prefiltered_pages_item->attributes = [
            'parent_id' => BackendMenu::find()->where(['name'=>'Shop'])->one()->id,
            'name' => 'Prefiltered pages',
            'route' => 'backend/prefiltered-pages/index',
            'icon' => 'filter',
            'sort_order' => '7',
            'added_by_ext' => 'core',
            'rbac_check' => 'shop manage',
            'css_class' => '',
            'translation_category' => 'app'
        ];
        $prefiltered_pages_item->save();

    }

    public function down()
    {
        $this->dropTable('{{%prefiltered_pages}}');
        $this->delete(BackendMenu::tableName(), ['name' => 'Prefiltered pages']);
    }
}
