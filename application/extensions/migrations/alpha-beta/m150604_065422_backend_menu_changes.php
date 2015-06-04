<?php

use yii\db\Schema;
use yii\db\Migration;

class m150604_065422_backend_menu_changes extends Migration
{
    public function up()
    {
        $this->update(\app\backend\models\BackendMenu::tableName(),
            ['route' => 'shop/backend-yml/settings'],
            ['route' => 'backend/yml/settings']
        );

        $this->update(\app\backend\models\BackendMenu::tableName(),
            ['route' => 'backend/config/index'],
            ['route' => 'config/backend/index']
        );

        $this->insert(\app\modules\config\models\Configurable::tableName(),
            [
                'module' => 'background',
                'sort_order' => 17,
                'section_name' => 'Background tasks',
                'display_in_config' => 1
            ]
        );
    }

    public function down()
    {
        echo "m150604_065422_backend_menu_changes cannot be reverted.\n";

        return false;
    }
}
