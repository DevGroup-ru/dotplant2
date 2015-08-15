<?php

use app\models\Object;
use app\widgets\navigation\models\Navigation;
use yii\db\Schema;
use yii\db\Migration;

class m150815_143352_navigation_object extends Migration
{
    public function up()
    {
        $this->insert(
            Object::tableName(),
            [
                'name' => 'Navigation',
                'object_class' => Navigation::className(),
                'object_table_name' => Yii::$app->db->schema->getRawTableName(Navigation::tableName()),
                'column_properties_table_name' => Yii::$app->db->schema->getRawTableName('{{%navigation_property}}'),
                'eav_table_name' => Yii::$app->db->schema->getRawTableName('{{%navigation_eav}}'),
                'categories_table_name' => Yii::$app->db->schema->getRawTableName('{{%navigation_category}}'),
                'link_slug_category' => Yii::$app->db->schema->getRawTableName('{{%navigation_category_full_slug}}'),
                'link_slug_static_value' => Yii::$app->db->schema->getRawTableName(
                    '{{%navigation_static_value_category}}'
                ),
                'object_slug_attribute' => 'slug',
            ]
        );
    }

    public function down()
    {
        $this->delete(Navigation::tableName(), ['name' => 'Navigation']);
    }

}
