<?php

namespace app\properties;


use app\models\ObjectStaticValues;
use app\models\Property;
use app\modules\shop\models\ConfigConfigurationModel;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

class PropertiesHelper
{

    /**
     * Добавляет к запросу фильтры по свойствам
     * @param $object \app\models\Object
     * @param $query ActiveQuery
     * @param $values_by_property_id array
     * @param array $dynamic_values_by_property_id array
     * @param string $multiFilterMode
     * @throws \Exception
     */
    public static function appendPropertiesFilters(
        $object,
        &$query,
        $values_by_property_id,
        $dynamic_values_by_property_id = [],
        $multiFilterMode = ConfigConfigurationModel::MULTI_FILTER_MODE_INTERSECTION
    )
    {

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
            $values = (array)$values;
            $property = Property::findById($property_id);
            if ($property === null) {
                continue;
            }
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
                switch ($multiFilterMode) {
                    case ConfigConfigurationModel::MULTI_FILTER_MODE_UNION:
                        $by_storage['static_values'][] = $values;
                        break;
                    default:
                        $by_storage['static_values'] = array_merge($by_storage['static_values'], $values);
                }
            } else {
                throw new \Exception("Wrong property type for " . $property->id);
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
                    throw new \Exception("Wrong property type for " . $property->id);
                }
            }
        }

        $ti_clauses = [];
        $join_table_name = "PropertiesJoinTable1";

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
                    Yii::$app->db->quoteValue((double)($item['values']['min']));
            }
            if (isset($item['values']['max']) && strlen($item['values']['max'])) {
                $clauses[] = "$join_table_name." .
                    Yii::$app->db->quoteColumnName($property->key) . " <= " .
                    Yii::$app->db->quoteValue((double)($item['values']['max']));
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

            switch ($multiFilterMode) {
                case ConfigConfigurationModel::MULTI_FILTER_MODE_UNION:
                    $subQuery = new Query();
                    $lastQuery = $subQuery;
                    $counter = 0;
                    foreach ($by_storage['static_values'] as $staticValues) {
                        $tmp = self::createSubQuery($object, $staticValues, ++$counter, $multiFilterMode);
                        $lastQuery->innerJoin(
                            ['osvm' . $counter => $tmp]
                            , 'osvm' . $counter . '.object_model_id = ' . 'osv' . ($counter - 1) . '.object_model_id'
                        );
                        $lastQuery = $tmp;
                    }

                    $query->innerJoin(
                        $subQuery->join[0][1]
                        , 'osvm1.object_model_id = ' . Yii::$app->db->quoteTableName($object->object_table_name) . '.id'
                    );

                    break;
                default:
                    $query->innerJoin(
                        ['osvm' => self::createSubQuery($object, $by_storage['static_values'])]
                        , 'osvm.object_model_id = ' . Yii::$app->db->quoteTableName($object->object_table_name) . '.id'
                    );
            }
        }

        if (count($by_storage['eav'])) {
            foreach ($by_storage['eav'] as $item) {
                $joinTableName = 'EAVJoinTable' . $item['property']->id;
                if (count($item['values']) > 0) {
                    $query = $query->innerJoin(
                        $object->eav_table_name . " " . $joinTableName,
                        "$joinTableName.object_model_id = " .
                        Yii::$app->db->quoteTableName($object->object_table_name) . ".id "
                    )->andWhere(
                        new Expression(
                            '`' . $joinTableName . '`.`value` in (' .
                            implode(', ', array_map('intval', $item['values'])) .
                            ') AND `' . $joinTableName . '`.`key` = "' . $item['property']->key . '"'
                        )
                    );
                }
            }
        }


    }

    /**
     * @param \app\models\Object $object
     * @param array $psvs
     * @param int $counter
     * @param string $multiFilterMode
     * @return Query
     */
    public static function createSubQuery(
        \app\models\Object $object,
        $psvs = [],
        $counter = 1,
        $multiFilterMode = ConfigConfigurationModel::MULTI_FILTER_MODE_INTERSECTION
    )
    {
        $tableInner = 'osv' . $counter;
        $psvsCount = count($psvs);
        switch ($multiFilterMode) {
            case ConfigConfigurationModel::MULTI_FILTER_MODE_UNION:
                if ($psvsCount > 1) {
                    $having = "count($tableInner.object_model_id) BETWEEN 1 AND $psvsCount";
                    break;
                }
                // no break in case of $psvsCount is 1 or 0
            default:
                $having = "count($tableInner.object_model_id) = $psvsCount";
        }
        $subQuery = (new Query)
            ->select("$tableInner.object_model_id")
            ->distinct()
            ->from(ObjectStaticValues::tableName() . " $tableInner")
            ->where([
                "$tableInner.object_id" => $object->id,
                "$tableInner.property_static_value_id" => $psvs
            ])
            ->groupBy("$tableInner.object_model_id")
            ->having($having);

        return $subQuery;
    }
}
