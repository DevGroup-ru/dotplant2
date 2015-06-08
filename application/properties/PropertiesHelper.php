<?php

namespace app\properties;


use app\models\Object;
use app\models\ObjectStaticValues;
use app\models\Property;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

class PropertiesHelper
{

    /**
     * Добавляет к запросу фильтры по свойствам
     * @param $object Object
     * @param $query ActiveQuery
     * @param $values_by_property_id array
     * @param array $dynamic_values_by_property_id array
     * @throws \Exception
     */
    public static function appendPropertiesFilters(
        $object,
        &$query,
        $values_by_property_id,
        $dynamic_values_by_property_id = []
    ) {

        /** @avr $object Object */

        // сначала сгруппируем свойства по типу хранения
        $by_storage = [
            'eav' => [],
            'table_inheritance' => [],
            'static_values' => [],
        ];
        $dynamic_by_storage = [
            'eav' => [],
            'table_inheritance' => [],
        ];
        foreach ($values_by_property_id as $property_id => $values) {
            // values может быть просто строкой(одно значение), а может уже прийти массивом
            $values = (array) $values;
            $property = Property::findById($property_id);
            if ($property===null) continue;
            if ($property->is_eav) {
                $by_storage['eav'][] = [
                    'property' => $property,
                    'values' => $values,
                ];
            } elseif ($property->is_column_type_stored) {
                $by_storage['table_inheritance'][] = [
                    'property' => $property,
                    'values' => $values,
                ];
            } elseif ($property->has_static_values) {
                $by_storage['static_values'][] = [
                    'property' => $property,
                    'values' => $values,
                ];
            } else {
                throw new \Exception("Wrong property type for ".$property->id);
            }
        }
        foreach ($dynamic_values_by_property_id as $property_id => $values) {
            $property = Property::findById($property_id);
            if ($property) {
                if ($property->is_eav) {
                    $dynamic_by_storage['eav'][] = [
                        'property' => $property,
                        'values' => $values,
                    ];
                } elseif ($property->is_column_type_stored) {
                    $dynamic_by_storage['table_inheritance'][] = [
                        'property' => $property,
                        'values' => $values,
                    ];
                } else {
                    throw new \Exception("Wrong property type for ".$property->id);
                }
            }
        }
        $join_counter = 1;
        $ti_clauses = [];
        $join_table_name = "PropertiesJoinTable".$join_counter++;
        foreach ($by_storage['table_inheritance'] as $item) {
            $property = $item['property'];
            $or_clauses = [];
            foreach ($item['values'] as $val) {
                $or_clauses[] = "$join_table_name." .
                    Yii::$app->db->quoteColumnName($property->key) . " = " .
                    Yii::$app->db->quoteValue($val);
            }
            $ti_clauses[] = implode(" OR ", $or_clauses);
        }
        foreach ($dynamic_by_storage['table_inheritance'] as $item) {
            $property = $item['property'];
            $clauses = [];
            if (isset($item['values']['min']) && strlen($item['values']['min'])) {
                $clauses[] = "$join_table_name." .
                    Yii::$app->db->quoteColumnName($property->key) . " >= " .
                    Yii::$app->db->quoteValue((double) ($item['values']['min']));
            }
            if (isset($item['values']['max']) && strlen($item['values']['max'])) {
                $clauses[] = "$join_table_name." .
                    Yii::$app->db->quoteColumnName($property->key) . " <= " .
                    Yii::$app->db->quoteValue((double) ($item['values']['max']));
            }
            if (!empty($clauses)) {
                $ti_clauses[] = '(' . implode(" AND ", $clauses) . ')';
            }
        }
        if (count($ti_clauses) > 0) {
            $ti_clauses = implode(" AND ", $ti_clauses);

            $query = $query->innerJoin(
                $object->column_properties_table_name . " $join_table_name",
                "$join_table_name.object_model_id = " .
                Yii::$app->db->quoteTableName($object->object_table_name) . ".id " .
                " AND " . $ti_clauses
            );
        }
        if (count($by_storage['static_values'])) {
            foreach ($by_storage['static_values'] as $item) {
                $joinTableName = 'OSVJoinTable'.$item['property']->id;
                if (count($item['values']) > 0) {
                    $query = $query->innerJoin(
                        ObjectStaticValues::tableName() . " " . $joinTableName,
                        "$joinTableName.object_id = " . intval($object->id) .
                        " AND $joinTableName.object_model_id = " .
                        Yii::$app->db->quoteTableName($object->object_table_name) . ".id "
                    );

                    $query = $query->andWhere(
                        new Expression(
                            '`' . $joinTableName . '`.`property_static_value_id` in (' .
                            implode(', ', array_map('intval', $item['values'])) .
                            ')'
                        )
                    );
                }
            }
        }
        if (count($by_storage['eav'])) {
            foreach ($by_storage['eav'] as $item) {
                $joinTableName = 'EAVJoinTable'.$item['property']->id;
                if (count($item['values']) > 0) {
                    $query = $query->innerJoin(
                        $object->eav_table_name . " " . $joinTableName,
                        "$joinTableName.object_model_id = " .
                        Yii::$app->db->quoteTableName($object->object_table_name) . ".id "
                    );
                    $query = $query->andWhere(
                        new Expression(
                            '`' . $joinTableName . '`.`value` in (' .
                            implode(', ', array_map('intval', $item['values'])) .
                            ') AND `' . $joinTableName . '`.`key` = "'. $item['property']->key.'"'
                        )
                    );
                }
            }
        }


    }
}