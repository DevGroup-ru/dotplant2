<?php

use yii\db\Schema;
use yii\db\Migration;

class m150408_092420_backend_menu_ratings extends Migration
{
    public function up()
    {
        $item = new \app\backend\models\BackendMenu;
        $item->attributes = [
            'parent_id' => 1,
            'name' => 'Rating groups',
            'route' => 'backend/rating/index',
            'icon' => 'star-half-o',
            'sort_order' => 6,
            'added_by_ext' => 'core',
            'rbac_check' => 'review manage',
            'css_class' => '',
            'translation_category' => 'app',
        ];
        $item->save();
    }

    public function down()
    {
        $this->delete(\app\backend\models\BackendMenu::tableName(), ['name' => 'Rating groups']);
    }
}
