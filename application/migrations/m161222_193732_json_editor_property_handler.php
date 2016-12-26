<?php

use app\models\PropertyHandler;
use app\properties\handlers\json\JsonProperty;
use yii\db\Migration;

class m161222_193732_json_editor_property_handler extends Migration
{
    public function up()
    {
        $this->insert(
            PropertyHandler::tableName(),
            [
                "name" => "Json editor",
                "frontend_render_view" => "frontend-render",
                "frontend_edit_view" => "frontend-edit",
                "backend_render_view" => "backend-render",
                "backend_edit_view" => "backend-edit",
                "handler_class_name" => JsonProperty::class
            ]
        );
    }

    public function down()
    {
        $this->delete(
            PropertyHandler::tableName(),
            [
                "handler_class_name" => JsonProperty::class
            ]
        );
    }
}
