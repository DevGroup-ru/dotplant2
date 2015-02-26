<?php

use app\backend\models\BackendMenu;
use yii\db\Migration;

class m150226_102422_reviews_menu extends Migration
{
    public function up()
    {
        $parent = BackendMenu::findOne(['parent_id' => 1, 'name' => 'Reviews']);
        if (!is_null($parent)) {
            $prods = new BackendMenu;
            $prods->attributes = [
                'parent_id' => $parent->id,
                'name' => 'Product reviews',
                'route' => 'backend/review/products',
                'icon' => 'comments',
                'sort_order' => '0',
                'added_by_ext' => 'core',
                'rbac_check' => 'review manage',
                'css_class' => '',
                'translation_category' => 'app',
            ];
            $prods->save();
            $pages = new BackendMenu;
            $pages->attributes = [
                'parent_id' => $parent->id,
                'name' => 'Page reviews',
                'route' => 'backend/review/pages',
                'icon' => 'comments-o',
                'sort_order' => '1',
                'added_by_ext' => 'core',
                'rbac_check' => 'review manage',
                'css_class' => '',
                'translation_category' => 'app',
            ];
            $pages->save();
            $parent->route = '';
            $parent->save(true, ['route']);
        }
    }

    public function down()
    {
        $parent = BackendMenu::findOne(['parent_id' => 1, 'name' => 'Reviews']);
        foreach ($parent->children as $child) {
            /** @var $child BackendMenu */
            $child->delete();
        }
        $parent->route = 'backend/review/index';
        $parent->save(true, ['route']);
    }
}
