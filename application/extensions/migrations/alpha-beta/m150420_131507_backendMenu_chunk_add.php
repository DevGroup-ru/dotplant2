<?php

use yii\db\Schema;
use yii\db\Migration;
use app\backend\models\BackendMenu;

class m150420_131507_backendMenu_chunk_add extends Migration
{
    public function up()
    {
        $currencies_page_item = new BackendMenu();
        $currencies_page_item->attributes = [
            'parent_id' => 1,
            'name' => 'Content Blocks',
            'route' => 'core/backend-chunk/index',
            'icon' => 'file-code-o',
            'sort_order' => 7,
            'added_by_ext' => 'core',
            'rbac_check' => 'content manage',
            'css_class' => '',
            'translation_category' => 'app'
        ];
        $currencies_page_item->save();
    }

    public function down()
    {
        $this->delete(BackendMenu::tableName(), ['name' => 'Content Blocks']);
    }
}
