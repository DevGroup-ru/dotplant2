<?php

use app\models\PropertyHandler;
use yii\db\Migration;

class m150326_124151_redactor extends Migration
{
    public function up()
    {
        $redactor = new PropertyHandler;
        $redactor->setAttributes(
            [
                'name' => 'Redactor',
                'frontend_render_view' => 'frontend-render',
                'frontend_edit_view' => 'frontend-edit',
                'backend_render_view' => 'backend-render',
                'backend_edit_view' => 'backend-edit',
                'handler_class_name' => 'app\properties\handlers\redactor\RedactorProperty',
            ]
        );
        $redactor->save();
    }

    public function down()
    {
        $this->delete(PropertyHandler::tableName(), ['name' => 'Redactor']);
    }
}
