<?php

use yii\db\Schema;
use yii\db\Migration;

class m141224_085939_hidden_property extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%property_handler}}',
            [
                'name' => 'Hidden',
                'frontend_render_view' => 'frontend-render',
                'frontend_edit_view' => 'frontend-edit',
                'backend_render_view' => 'backend-render',
                'backend_edit_view' => 'backend-edit',
                'handler_class_name' => 'app\properties\handlers\hidden\HiddenProperty',
            ]
        );
    }

    public function down()
    {
        $this->execute('DELETE FROM {{%property_handler}} WHERE name=:name', [':name'=>'Hidden']);
    }
}
