<?php

use yii\db\Migration;

class m141222_151034_slider_backend_menu_item extends Migration
{
    public function up()
    {
        $slider_item = new \app\backend\models\BackendMenu();
        $slider_item->attributes = [
            'parent_id' => '1',
            'name' => 'Sliders',
            'route' => 'backend/slider/index',
            'icon' => 'arrows-h',
            'sort_order' => '10',
            'added_by_ext' => 'core',
            'rbac_check' => 'content manage',
            'css_class' => '',
            'translation_category' => 'app'
        ];
        $slider_item->save();
    }

    public function down()
    {
        $this->delete(\app\backend\models\BackendMenu::tableName(), ['name' => 'Sliders']);
    }
}
