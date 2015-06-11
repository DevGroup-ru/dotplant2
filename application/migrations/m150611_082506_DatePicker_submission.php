<?php

use app\models\PropertyHandler;
use yii\db\Migration;

class m150611_082506_DatePicker_submission extends Migration
{
    public function up()
    {
        $this->insert(
            PropertyHandler::tableName(),
            [
                'name' => 'DatePicker',
                'frontend_render_view' => 'frontend-render',
                'frontend_edit_view' => 'frontend-edit',
                'backend_render_view' => 'backend-render',
                'backend_edit_view' => 'backend-edit',
                'handler_class_name' => 'app\properties\handlers\datepicker\DatepickerProperty',
            ]
        );
    }

    public function down()
    {
        $this->delete(PropertyHandler::tableName(), ['name' => 'DatePicker',]);
    }

}

