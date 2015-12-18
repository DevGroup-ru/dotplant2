<?php

use app\backend\models\BackendMenu;
use yii\db\Migration;

class m151207_103155_google_merchants extends Migration
{
    public function up()
    {
        $shopMenuItem = BackendMenu::findOne([
            'name' => 'Settings',
        ]);

        $this->insert(
            BackendMenu::tableName(),
           [
               'parent_id' => $shopMenuItem->id,
               'name' => 'Google Merchants',
               'route' => '/shop/backend-google-feed/settings',
               'icon' => 'google',
               'sort_order' => '0',
               'added_by_ext' => 'core',
               'rbac_check' => 'content manage',
               'css_class' => Null,
               'translation_category' => 'app',
           ]
        );

    }

    public function down()
    {
        $this->delete(
            BackendMenu::tableName(),
            [
                'route' => '/shop/backend-google-feed/settings'
            ]
        );
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
