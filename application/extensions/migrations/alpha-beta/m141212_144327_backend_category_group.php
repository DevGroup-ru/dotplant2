<?php

use yii\db\Schema;
use yii\db\Migration;

class m141212_144327_backend_category_group extends Migration
{
    public function up()
    {
        $shop = \app\backend\models\BackendMenu::findOne(['name' => 'Shop']);
        if (!is_null($shop)) {
            $item = new \app\backend\models\BackendMenu;
            $item->attributes = [
                'parent_id' => $shop->id,
                'name' => 'Categories groups',
                'route' => 'backend/category-group/index',
                'icon' => 'tag',
                'sort_order' => 0,
                'added_by_ext' => 'core',
                'rbac_check' => 'category manage',
                'css_class' => '',
                'translation_category' => 'shop',
            ];
            $item->save();
        }
    }

    public function down()
    {
        \app\backend\models\BackendMenu::deleteAll(['route' => 'backend/category-group/index']);
    }
}
