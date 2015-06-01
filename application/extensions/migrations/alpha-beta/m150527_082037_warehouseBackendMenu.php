<?php

use yii\db\Schema;
use yii\db\Migration;

class m150527_082037_warehouseBackendMenu extends Migration
{
    public function up()
    {
        $menu = \app\backend\models\BackendMenu::findOne(['name' => 'Shop']);
        if (!is_null($menu)) {
            $this->insert(
                \app\backend\models\BackendMenu::tableName(),
                [
                    'parent_id' => $menu->id,
                    'name' => 'Warehouse',
                    'route' => '/shop/backend-warehouse/index',
                    'icon' => 'cubes',
                    'sort_order' => '15',
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
                'name' => 'Warehouse',
                'route' => '/shop/backend-warehouse/index',
            ]
        );
    }
    

}
