<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\backend\models\BackendMenu;

class m150224_081305_currencies_backend_menu extends Migration
{
    public function up()
    {
        $currencies_page_item = new BackendMenu();
        $currencies_page_item->attributes = [
            'parent_id' => BackendMenu::find()->where(['name'=>'Shop'])->one()->id,
            'name' => 'Currencies',
            'route' => 'backend/currencies/index',
            'icon' => 'dollar',
            'sort_order' => '8',
            'added_by_ext' => 'core',
            'rbac_check' => 'shop manage',
            'css_class' => '',
            'translation_category' => 'app'
        ];
        $currencies_page_item->save();
    }

    public function down()
    {
        $this->delete(BackendMenu::tableName(), ['name' => 'Currencies']);
    }
}
