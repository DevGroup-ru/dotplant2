<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\backend\models\BackendMenu;

class m150203_085736_yml_menu extends Migration
{
    public function up()
    {
        $mb = BackendMenu::find()->where(['name' => 'Settings', 'parent_id' => 1])->one();

        $this->insert(BackendMenu::tableName(),
            [
                'parent_id' => $mb->id,
                'name' => 'YML',
                'route' => 'backend/yml/settings',
                'icon' => 'code',
                'added_by_ext' => 'core',
                'rbac_check' => 'content manage',
                'translation_category' => 'app'
            ]
        );

    }

    public function down()
    {
        $this->delete(BackendMenu::tableName(),
            [
                'name' => 'YML',
                'route' => 'backend/yml/settings',
                'icon' => 'code',
                'added_by_ext' => 'core',
                'rbac_check' => 'content manage',
                'translation_category' => 'app'
            ]
        );

        return true;
    }
}
