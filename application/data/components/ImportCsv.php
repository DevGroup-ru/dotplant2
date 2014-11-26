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
                        if (!empty($this->propertyMultipleValuesDelimiter)) {
                            if (strpos($propValue, $this->propertyMultipleValuesDelimiter) > 0) {
                                $values = explode($this->propertyMultipleValuesDelimiter, $propValue);
                                $propValue = [];
                                foreach($values as $value) {
                                    $value = trim($value);
                                    if (!empty($value)) {
                                        $propValue[] = $value;
                                    }
                                }
                            }
                        }
                        $propData[$attribute] = $propValue;
                    }
                    $objectId = isset($titleFields['internal_id']) ? $row[$titleFields['internal_id']] : 0;
                    $this->save($objectId, $objData, $importFields['object'], $propData, $importFields['property']);
                }
                fclose($file);
            } catch (\Exception $e) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        }
        if (file_exists($path)) {
            unlink($path);
        }
        return true;
    }

    public function getData($exportFields)
    {
        $objectFields = isset($exportFields['object']) ? $exportFields['object'] : [];
        $propertiesFields = isset($exportFields['property']) ? $exportFields['property'] : [];
        $class = $this->object->object_class;
        $objectFields = array_merge($objectFields, ['internal_id']);
        $title = array_merge($objectFields, $propertiesFields);
        $output = fopen(Yii::$app->getModule('data')->exportDir . '/' . $this->filename, 'w');
        $objects = $class::find()->all();
        fputcsv($output, $title);
        foreach ($objects as $object) {
            $row = [];
            foreach ($objectFields as $field) {
                if ($field === 'internal_id') {
                    $row[] = $object->id;
                } else {
                    $row[] = isset($object->$field) ? $object->$field : '';
                }
            }
            foreach ($propertiesFields as $field) {
                $row[] = $object->getPropertyValuesByKey($field);
            }
            fputcsv($output, $row);
        }
        fclose($output);
    }
}
