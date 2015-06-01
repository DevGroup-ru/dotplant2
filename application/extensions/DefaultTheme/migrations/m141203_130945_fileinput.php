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
        $core = \app\models\Config::find()->where(['name' => 'Core'])->one();
        $this->insert(\app\models\Config::tableName(), [
            'parent_id' => $core->id,
            'name' => 'Path to user uploaded files',
            'key' => 'fileUploadPath',
            'value' => 'upload/user-uploads/',
            'preload' => 0,
            'path' => $core->path . '.fileUploadPath',
        ]);
    }

    public function down()
    {
        $this->delete(\app\models\PropertyHandler::tableName(), ['name' => 'File']);
        $this->delete(\app\models\Config::tableName(), ['name' => 'fileUploadPath']);
    }
}
