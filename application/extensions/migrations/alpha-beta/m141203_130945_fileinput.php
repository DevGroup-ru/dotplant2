<?php

use yii\db\Migration;

class m141203_130945_fileinput extends Migration
{
    public function up()
    {
        $this->insert(\app\models\PropertyHandler::tableName(), [
            'name' => 'File',
            'frontend_render_view' => 'frontend-render',
            'frontend_edit_view' => 'frontend-edit',
            'backend_render_view' => 'backend-render',
            'backend_edit_view' => 'backend-edit',
            'handler_class_name' => 'app\properties\handlers\fileInput\FileInputProperty',
        ]);
    }

    public function down()
    {
        $this->delete(\app\models\PropertyHandler::tableName(), ['name' => 'File']);
    }
}
