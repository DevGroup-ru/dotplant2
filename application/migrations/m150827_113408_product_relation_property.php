<?php

use app\models\PropertyHandler;
use app\properties\handlers\productRelation\ProductRelationProperty;
use yii\db\Migration;

class m150827_113408_product_relation_property extends Migration
{
    public function up()
    {
        $this->insert(
            PropertyHandler::tableName(),
            [
                'name' => 'Product relation',
                'frontend_render_view' => 'frontend-render',
                'frontend_edit_view' => 'frontend-edit',
                'backend_render_view' => 'backend-render',
                'backend_edit_view' => 'backend-edit',
                'handler_class_name' => ProductRelationProperty::class,
            ]
        );
    }

    public function down()
    {
        $this->delete(
            PropertyHandler::tableName(),
            [
                'handler_class_name' => ProductRelationProperty::class,
            ]
        );
    }
}
