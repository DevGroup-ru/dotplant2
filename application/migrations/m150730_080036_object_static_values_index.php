<?php

use app\models\ObjectStaticValues;
use yii\db\Migration;

class m150730_080036_object_static_values_index extends Migration
{
    public function up()
    {
        $this->createIndex(
            'ix-object_static_values-property_static_value_id',
            ObjectStaticValues::tableName(),
            ['property_static_value_id']
        );
    }

    public function down()
    {
        $this->dropIndex('ix-object_static_values-property_static_value_id', ObjectStaticValues::tableName());
    }
}
