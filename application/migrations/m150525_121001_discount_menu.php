<?php

use yii\db\Schema;
use yii\db\Migration;

class m150525_121001_discount_menu extends Migration
{
    public function up()
    {
        $menu = \app\backend\models\BackendMenu::findOne(['name' => 'Shop']);
        if (!is_null($menu)) {
            $this->insert(
                \app\backend\models\BackendMenu::tableName(),
                [
                    'parent_id' => $menu->id,
                    'name' => 'Discounts',
                    'route' => '/shop/backend-discount/index',
                    'icon' => 'shekel',
                    'sort_order' => '10',
                    'added_by_ext' => 'core',
                    'rbac_check' => 'shop manage',
                    'css_class' => '',
                    'translation_category' => 'app',
                ]
            );
        }
    }

    public function down()
    {
        $this->delete(
            \app\backend\models\BackendMenu::tableName(),
            [
                'name' => 'Discounts',
                'route' => '/shop/backend-discount/index'
            ]
        );
    }

}
