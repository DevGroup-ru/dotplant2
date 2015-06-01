<?php

use app\models\Object;
use yii\db\Migration;

class m141119_125348_column_properties extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%category_property}}',
            [
                'object_model_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY',
            ],
            $tableOptions
        );
        $this->update(Object::tableName(), ['column_properties_table_name' => 'category_property'], ['id' => 2 ]);
        $this->update(Object::tableName(), ['column_properties_table_name' => 'product_property'], ['id' => 3 ]);
    }

    public function down()
    {
        $this->dropTable('{{%category_property}}');
        $this->update(Object::tableName(), ['column_properties_table_name' => 'category_properties'], ['id' => 2]);
        $this->update(Object::tableName(), ['column_properties_table_name' => 'product_properties'], ['id' => 3]);
    }
}
