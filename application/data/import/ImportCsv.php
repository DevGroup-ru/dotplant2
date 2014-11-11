<?php

namespace app\data\import;

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
        if (isset($fields['object'])) {
            $objAttributes = $fields['object'];
            $propAttributes = isset($fields['property']) ? $fields['property'] : [];
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $titleFields = [];
                $title = true;
                while (($row = fgetcsv($this->file)) !== false) {
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
                        $propData[$attribute] = (isset($titleFields[$attribute])) ? $row[$titleFields[$attribute]] : '';
                    }
                    $objectId = isset($titleFields['internal_id']) ? $row[$titleFields['internal_id']] : 0;
                    $this->save($objectId, $objData, $importFields['object'], $propData, $importFields['property']);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        }
        return true;
    }

    public function getData($exportFields)
    {
        $name = $this->object->name . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $name);
        $objectFields = isset($exportFields['object']) ? $exportFields['object'] : [];
        $propertiesFields = isset($exportFields['property']) ? $exportFields['property'] : [];
        $class = $this->object->object_class;
        $objectFields = array_merge($objectFields, ['internal_id']);
        $title = array_merge($objectFields, $propertiesFields);
        $output = fopen('php://output', 'w');
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
        \Yii::$app->end();
    }
}
