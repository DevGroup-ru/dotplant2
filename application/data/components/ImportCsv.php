<?php

namespace app\data\components;

use Yii;

class ImportCsv extends Import
{
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
                while (($row = fgetcsv($file)) !== false) {
                    if ($title) {
                        $titleFields = array_flip($row);
                        $title = false;
                        continue;
                    }
                    $objData = [];
                    $propData = [];
                    foreach ($objAttributes as $attribute) {
                        $objData[$attribute] = (isset($titleFields[$attribute])) ? $row[$titleFields[$attribute]] : '';
                    }
                    foreach ($propAttributes as $attribute) {
                        $propValue = (isset($titleFields[$attribute])) ? $row[$titleFields[$attribute]] : '';
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

    public function getData($exportFields, $batchSize = 100)
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

        fputcsv($output, $title);

        /** @var array $propertyIds Array of propertyIds to export */
        $propertyIds = array_keys($propertiesFields);

        $objects = $class::find();
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

                if (count($value->values) > 1 && isset($propertiesFields[$propertyId])) {
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

            fputcsv($output, $row);
        }
        gc_enable();
        fclose($output);
    }
}
