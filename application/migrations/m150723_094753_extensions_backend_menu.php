<?php

use yii\db\Schema;
use yii\db\Migration;

class m150723_094753_extensions_backend_menu extends Migration
{
    public function up()
    {
        $tblMenu = '{{%backend_menu}}';
        $settingsMenuItem = \app\backend\models\BackendMenu::findOne([
            'name' => 'Settings',
        ]);
        /** @var \app\backend\models\BackendMenu $settingsMenuItem */
        if (null === $settingsMenuItem) {
            echo 'Where is the Settings menu item?';
            return false;
        }

        $this->batchInsert($tblMenu,
            [
                'parent_id',
                'name',
                'route',
                'icon',
                'sort_order',
                'added_by_ext',
                'rbac_check',
                'css_class',
                'translation_category'
            ],
            [
                [$settingsMenuItem->id, 'Extensions', 'core/backend-extensions/index', 'puzzle-piece', 0, 'core', 'setting manage', '', 'app'],
            ]
        );
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\backend\models\BackendMenu::className())
            ]
        );
        return true;
    }

    public function down()
    {
        $this->delete('{{%backend_menu}}', ['name' => 'Extensions']);
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\backend\models\BackendMenu::className())
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
