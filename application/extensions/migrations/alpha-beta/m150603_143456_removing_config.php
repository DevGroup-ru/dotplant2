<?php

use app\backend\models\BackendMenu;
use yii\db\Migration;

class m150603_143456_removing_config extends Migration
{
    public function up()
    {
        return true;
        die('Do not apply this migration');
        $errorMonitor = BackendMenu::findOne(['name' => 'Error monitoring']);
        BackendMenu::deleteAll(['parent_id' => $errorMonitor->id]);
        $errorMonitor->route = 'backend/error-monitor/index';
        $errorMonitor->save();
    }

    public function down()
    {
        $errorMonitor = BackendMenu::findOne(['name' => 'Error monitoring']);
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'icon', 'added_by_ext', 'rbac_check'],
            [
                [$errorMonitor->id, 'Monitor', 'backend/error-monitor/index', 'flash', 'core', 'setting manage'],
                [$errorMonitor->id, 'Config', 'backend/error-monitor/config', 'gear', 'core', 'setting manage'],
            ]
        );
        $errorMonitor->route = '';
        $errorMonitor->save();
    }

}
