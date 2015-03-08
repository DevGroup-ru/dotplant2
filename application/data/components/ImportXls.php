<?php

namespace app\data\components;

use Yii;
use PHPExcel;
use \PHPExcel_IOFactory;
use \PHPExcel_Style_Border;

class ImportXls extends Import
{
	protected function getCoords($letters, $num)
	{
		$coords = array_map(
			function($code){ return chr($code); },
			$letters
		);
		$coords = implode('', $coords);
		$coords .= $num;

		return $coords;
	}

	protected function increaseCoords($coords)
	{
		end($coords);
		$coords[key($coords)]++;

		if (end($coords) > 90) {
			$coords[key($coords)] = 65;

			if (is_int(prev($coords)) && prev($coords) < 90) {
				$coords[key($coords)]++;
			} else {
				array_unshift($coords, 65);
			}
		}

		reset($coords);

		return $coords;
	}

    protected function getXls($filename)
    {
		$xls = PHPExcel_IOFactory::load($filename);
		return $xls->getActiveSheet()->toArray(null, true, true, false); 
    }

    protected function putXls($filename, $data)
    {
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);

		$row = 1;
		$letters = [65]; // The code of the 'A' letter.
		$border_style = [
			'borders' => [
				'outline' => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
					'color' => array('argb' => 'FF000000'),
				],
			],
		];

		foreach($data['head'] as $th) {
			$coords = $this->getCoords($letters, $row);
			$objPHPExcel->getActiveSheet()->SetCellValue($coords, $th);
			$objPHPExcel->getActiveSheet()->getStyle($coords)->applyFromArray($border_style);

			$letters = $this->increaseCoords($letters);
		}
		unset($th);

		foreach($data['body'] as $td) {
			$row++;
			$letters = [65];

			foreach($td as $val) {
				$coords = $this->getCoords($letters, $row);
				$objPHPExcel->getActiveSheet()->SetCellValue($coords, $val);
				$letters = $this->increaseCoords($letters);
			}
			unset($val);
		}
		unset($td);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save($filename);
    }

	public function setData($importFields)
	{
		if (!isset($importFields['object'])) {
			$importFields['object'] = [];
		}
		if (!isset($importFields['property'])) {
			$importFields['property'] = [];
		}

		$fields = static::getFields($this->object->id);
		$filename = Yii::$app->getModule('data')->importDir . '/' . $this->filename;

		if (isset($fields['object'])) {
			$objAttributes = $fields['object'];
			$propAttributes = isset($fields['property']) ? $fields['property'] : [];
			$transaction = \Yii::$app->db->beginTransaction();

			try {
				$data = $this->getXls($filename);
				$titleFields = array_shift($data);
				$titleNums = array_flip($titleFields);

				foreach($data as $product) {
					$objData = [];
					$propData = [];

					foreach ($objAttributes as $attribute) {
						if (isset($titleNums[$attribute])) {
							$objData[$attribute] = $product[$titleNums[$attribute]];
						}
					}

					foreach ($propAttributes as $attribute) {
						if (!(isset($titleNums[$attribute]))) {
							continue;
						}

						$propValues = $product[$titleNums[$attribute]];

						if (!empty($this->multipleValuesDelimiter)) {
							if (strpos($propValues, $this->multipleValuesDelimiter) > 0) {
								$values = explode($this->multipleValuesDelimiter, $propValues);
							} elseif (strpos($this->multipleValuesDelimiter, '/') === 0) {
								$values = preg_split($this->multipleValuesDelimiter, $propValues);
							} else {
								$values = [$propValues];
							}

							$propValues = [];

							foreach($values as $value) {
								$value = trim($value);
								if (!empty($value)) {
									$propValues[] = $value;
								}
							}
						}
						$propData[$attribute] = $propValues;
					}

					$objectId = isset($titleNums['internal_id']) ? $product[$titleNums['internal_id']] : 0;
					$this->save($objectId, $objData, $importFields['object'], $propData, $importFields['property'], $product, $titleNums);
				}
				unset($product);
			} catch (\Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
			$transaction->commit();
		}

		//if (file_exists($filename)) {
			//unlink($filename);
		//}

		return true;
	}

	public function getData($exportFields)
	{
		$objectFields		= isset($exportFields['object']) ? $exportFields['object'] : [];
		$propertiesFields	= isset($exportFields['property']) ? $exportFields['property'] : [];
		$additionalFields	= isset($exportFields['additionalFields']) ? $exportFields['additionalFields'] : [];

		array_unshift($objectFields, 'internal_id');

		$propertiesKeys = [];
		foreach ($propertiesFields as $field) {
			$propertiesKeys[] = $field['key'];
		}

		$data['head'] = array_merge($objectFields, $propertiesKeys, array_keys($additionalFields));

		$filename = Yii::$app->getModule('data')->exportDir . DIRECTORY_SEPARATOR . $this->filename;

		$propertyIds = array_keys($propertiesFields);

		$class = $this->object->object_class;
		$objects = $class::find();

		$data['body'] = [];

		foreach ($objects->each() as $object) {
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
					if (isset($propertiesFields[$propertyId]['processValuesAs'])) {
						$representationConversions = [
							'text' => 'name',
							'value' => 'value',
							'id' => 'psv_id',
						];
						$attributeToGet = $representationConversions[$propertiesFields[$propertyId]['processValuesAs']];
						$newValues = [];
						foreach ($value->values as $val) {
							$newValues[] = $val[$attributeToGet];
						}
						unset($val);
						$value = implode($this->multipleValuesDelimiter, $newValues);
					}
				} else {
					$value = (string) $value;
				}

				$row[] = $value;
			}
			unset($propertyId);

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
							$row[] = '';
						}
					}
				}
				unset($key, $configuration);
			}

			$data['body'][] = $row;
		}

		if ($data['body']) {
			$this->putXls($filename, $data);
		}
	}
}
