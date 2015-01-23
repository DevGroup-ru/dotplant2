<?php

use yii\db\Schema;
use yii\db\Migration;

class m141125_084200_data_backend_menu_fix extends Migration
{
    public function up()
    {
        $this->execute("DELETE FROM {{%backend_menu}} WHERE parent_id = 16 OR id = 16");

        $this->insert(
            '{{%backend_menu}}',
            [
                'parent_id' => '18',
                'name' => 'Data',
                'route' => 'data/file/index',
                'icon' => 'database',
                'sort_order' => '0',
                'added_by_ext' => 'core',
                'rbac_check' => 'data manage',
                'css_class' => '',
                'translation_category' => 'app'
            ]
        );

    }

    public function down()
    {

    }
}
