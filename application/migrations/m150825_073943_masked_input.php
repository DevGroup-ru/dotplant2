<?php

use app\models\Property;
use app\models\PropertyHandler;
use yii\db\Schema;
use yii\db\Migration;

class m150825_073943_masked_input extends Migration
{
    public function up()
    {
        $this->insert(
            PropertyHandler::tableName(),
            [
                'name' => 'MaskedInput',
                'frontend_render_view' => 'frontend-render',
                'frontend_edit_view' => 'frontend-edit',
                'backend_render_view' => 'backend-render',
                'backend_edit_view' => 'backend-edit',
                'handler_class_name' => 'app\properties\handlers\maskedinput\MaskedinputProperty',
            ]
        );
        $this->addColumn(Property::tableName(), 'mask', $this->string());
        $this->addColumn(Property::tableName(), 'alias', $this->smallInteger());
    }

    public function down()
    {
        $this->delete(PropertyHandler::tableName(), ['name' => 'MaskedInput',]);
        $this->dropColumn(Property::tableName(), 'mask');
        $this->dropColumn(Property::tableName(), 'alias');
    }

}
