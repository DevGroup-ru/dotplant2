<?php

namespace app\data\components;

use app\models\Property;
use app\models\Object;
use app\models\Product;
use yii\db\Expression;
use app\models\PropertyStaticValues;
use app\models\ObjectStaticValues;
use Yii;

abstract class AbstractImportCsv extends Import
{
    abstract protected function getCsv($handle);
    abstract protected function putCsv($handle, $fields);
    abstract protected function putHeader($handle, $fields);

    public function setData($importFields)
    {

        if (!isset($importFields['object'])) {
            $importFields['object'] = [];
        }
        if (!isset($importFields['property'])) {
            $importFields['property'] = [];
        }
        $fields = static::getFields($this->object->id);
        $path = Yii::$app->getModule('data')->importDir . '/' . $this->filename;
        if (isset($fields['object'])) {
            $objAttributes = $fields['object'];
            $propAttributes = isset($fields['property']) ? $fields['property'] : [];
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $titleFields = [];
                $file = fopen($path, 'r');
                $title = true;
                while (($row = $this->getCsv($file)) !== false) {
                    if ($title) {
                        $titleFields = array_flip($row);
                        $title = false;
                        continue;
                    }
                    $objData = [];
                    $propData = [];
                    foreach ($objAttributes as $attribute) {
                        if (isset($titleFields[$attribute])) {
                            $objData[$attribute] = $row[$titleFields[$attribute]];
                        }
                    }
                    foreach ($propAttributes as $attribute) {
                        if (!(isset($titleFields[$attribute]))) {
                            continue;
                        }
                        $propValue = $row[$titleFields[$attribute]];
                        if (!empty($this->multipleValuesDelimiter)) {

                            if (strpos($propValue, $this->multipleValuesDelimiter) > 0) {
                                $values = explode($this->multipleValuesDelimiter, $propValue);
                            } elseif (strpos($this->multipleValuesDelimiter, '/') === 0) {
                                $values = preg_split($this->multipleValuesDelimiter, $propValue);
                            } else {
                                $values = [$propValue];
                            }
                            $propValue = [];
                            foreach($values as $value) {
                                $value = trim($value);
                                if (!empty($value)) {
                                    $propValue[] = $value;
                                }
                            }
                        }
                        $propData[$attribute] = $propValue;
                    }
                    $objectId = isset($titleFields['internal_id']) ? $row[$titleFields['internal_id']] : 0;
                    $this->save($objectId, $objData, $importFields['object'], $propData, $importFields['property'], $row, $titleFields);
                }
                fclose($file);
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            $transaction->commit();
        }
        if (file_exists($path)) {
            unlink($path);
        }
        return true;
    }

    public function getData($exportFields, $batchSize = 25, $conditions = [])
    {
        $objectFields = isset($exportFields['object']) ? $exportFields['object'] : [];
        $propertiesFields = isset($exportFields['property']) ? $exportFields['property'] : [];

        $additionalFields = isset($exportFields['additionalFields']) ? $exportFields['additionalFields'] : [];

        $class = $this->object->object_class;
        $objectFields = array_merge($objectFields, ['internal_id']);

        /** @var array $propertiesKeys used for titles */
        $propertiesKeys = [];
        foreach ($propertiesFields as $field) {
            $propertiesKeys[] = $field['key'];
        }

        $title = array_merge($objectFields, $propertiesKeys, array_keys($additionalFields));



        $output = fopen(Yii::$app->getModule('data')->exportDir . '/' . $this->filename, 'w');

        $this->putHeader($output, $title);

        /** @var array $propertyIds Array of propertyIds to export */
        $propertyIds = array_keys($propertiesFields);

        $objects = $class::find();

        if (
            isset($conditions['category']) &&
            is_array($conditions['category']) &&
            $this->object->id == Object::getForClass(Product::className())->id
        ) {
            foreach ($conditions['category'] as $condition) {
                $joinTableName = 'Category'.$condition['value'];

                $objects->innerJoin(
                    "{{%product_category}} " . $joinTableName,
                    "$joinTableName.object_model_id = product.id"
                );
                $objects->andWhere(
                    new Expression(
                        '`' . $joinTableName . '`.`category_id` = "'.$condition['value'].'"'
                    )
                );
            }
        }

        if (isset($conditions['field']) && is_array($conditions['field'])) {
            foreach ($conditions['field'] as $condition) {
                $conditionOptions = [$condition['operators'], $condition['value'], $condition['option']];
                if ($condition['comparison'] == 'AND') {
                    $objects->andWhere($conditionOptions);
                } elseif ($condition['comparison'] == 'OR') {
                    $objects->orWhere($conditionOptions);
                }
            }
        }
        if (isset($conditions['property']) && is_array($conditions['property'])) {
            foreach ($conditions['property'] as $condition) {

                $property = Property::findById($condition['value']);

                if ($property && isset($condition['option']) &&  !empty($condition['option'])) {
                    if ($property->is_eav) {
                        $joinTableName = 'EAVJoinTable'.$property->id;

                        $objects->innerJoin(
                            $this->object->eav_table_name . " " . $joinTableName,
                            "$joinTableName.object_model_id = " .
                            Yii::$app->db->quoteTableName($this->object->object_table_name) . ".id "
                        );
                        $objects->andWhere(
                            new Expression(
                                '`' . $joinTableName . '`.`value` '.$condition['operators'].' "'.$condition['option'].'" AND `' .
                                $joinTableName . '`.`key` = "'. $property->key.'"'
                            )
                        );

                    } elseif ($property->has_static_values) {
                        $joinTableName = 'OSVJoinTable'.$property->id;
                        $propertyStaticValue = PropertyStaticValues::find()->where(['value'=>$condition['option']])->one();

                        if ($propertyStaticValue) {
                            $objects->innerJoin(
                                ObjectStaticValues::tableName() . " " . $joinTableName,
                                "$joinTableName.object_id = " . intval($this->object->id) .
                                " AND $joinTableName.object_model_id = " .
                                Yii::$app->db->quoteTableName($this->object->object_table_name) . ".id "
                            );

                            $objects->andWhere(
                                new Expression(
                                    '`' . $joinTableName . '`.`property_static_value_id` ="'.$propertyStaticValue->id.'"'
                                )
                            );
                        }
                    } else {
                        throw new \Exception("Wrong property type for ".$property->id);
                    }
                }
            }
        }



        gc_disable();
        foreach ($objects->each($batchSize) as $object) {
            $row = [];
            foreach ($objectFields as $field) {
                if ($field === 'internal_id') {
                    $row[] = $object->id;
                } else {
                    $row[] = isset($object->$field) ? $object->$field : '';
                }
            }

            foreach ($propertyIds as $propertyId) {
                $value = $object->getPropertyValuesByPropertyId($propertyId);
                if (!is_object($value)) {
                    $value = '';
                } elseif (count($value->values) > 1 && isset($propertiesFields[$propertyId])) {
                    // we should implode
                    // respecting processValueAs
                    if (isset($propertiesFields[$propertyId]['processValuesAs'])) {
                        $representationConversions = [
                            // from -> to
                            'text' => 'name',
                            'value' => 'value',
                            'id' => 'psv_id',
                        ];
                        $attributeToGet = $representationConversions[$propertiesFields[$propertyId]['processValuesAs']];
                        $newValues = [];
                        foreach ($value->values as $val) {
                            $newValues[] = $val[$attributeToGet];
                        }
                        $value = implode($this->multipleValuesDelimiter, $newValues);
                    }
                } else {
                    $value = (string) $value; // костыль
                }
                $row[] = $value;
            }

            if (count($additionalFields) > 0 && $object->hasMethod('getAdditionalFields')) {
                $fieldsFromModel = $object->getAdditionalFields($additionalFields);
                foreach ($additionalFields as $key => $configuration) {
                    if ($configuration['enabled']) {
                        if (!isset($fieldsFromModel[$key])) {
                            $fieldsFromModel[$key] = '';
                        }

                        if (!empty($fieldsFromModel[$key])) {
                            $value = (array)$fieldsFromModel[$key];
                            $row[] = implode($this->multipleValuesDelimiter, $value);
                        } else {
                            // empty
                            $row[] = '';
                        }
                    }
                }
            }

            $this->putCsv($output, $row);
            gc_enable();
            gc_collect_cycles();
            gc_disable();
        }
        gc_enable();
        fclose($output);
    }
}
