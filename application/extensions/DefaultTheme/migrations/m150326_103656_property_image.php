<?php

use app\models\Object;
use app\models\Property;
use app\models\PropertyStaticValues;
use yii\db\Migration;

class m150326_103656_property_image extends Migration
{
    public function up()
    {
        $this->batchInsert(
            Object::tableName(),
            [
                'name',
                'object_class',
                'object_table_name',
                'column_properties_table_name',
                'eav_table_name',
                'categories_table_name',
                'link_slug_category',
                'link_slug_static_value',
                'object_slug_attribute',
            ],
            [
                [
                    'Property',
                    Property::className(),
                    Yii::$app->db->schema->getRawTableName(Property::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%property_property}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_value_category}}'),
                    'slug',
                ],
                [
                    'PropertyStaticValues',
                    PropertyStaticValues::className(),
                    Yii::$app->db->schema->getRawTableName(PropertyStaticValues::tableName()),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_properties}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_eav}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_category}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_category_full_slug}}'),
                    Yii::$app->db->schema->getRawTableName('{{%property_static_values_static_value_category}}'),
                    'slug',
                ],
            ]
        );
    }

    public function down()
    {
        $this->delete(
            Object::tableName(),
            [
                'object_class' => [
                    Property::className(),
                    PropertyStaticValues::className(),
                ]
            ]
        );
    }
}
