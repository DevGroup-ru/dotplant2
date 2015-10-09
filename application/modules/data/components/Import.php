<?php

namespace app\modules\data\components;

use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\modules\shop\models\Product;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

abstract class Import extends Component
{
    protected $object;
    protected $properties = null;
    public $filename;
    public $addPropertyGroups = [];
    public $createIfNotExists = false;
    public $multipleValuesDelimiter = '|';
    public $additionalFields = [];

    /*
     * Export method
     */
    abstract public function getData($header, $data);
    /*
     * Import method
     */
    abstract public function setData();

    /**
     * @param array $config
     * @return ImportCsv
     * @throws \Exception
     */
    public static function createInstance($config)
    {
        if (isset($config['type'])) {
            $type = $config['type'];
            unset($config['type']);

            switch ($type) {
                case 'csv':
                    return new ImportCsv($config);
                case 'excelCsv':
                    return new ImportExcelCsv($config);
                case 'xls':
                    return new ImportXlsx(array_merge(['fileType' => 'xls'], $config));
                case 'xlsx':
                    return new ImportXlsx(array_merge(['fileType' => 'xlsx'], $config));
                default:
                    throw new \Exception('Unsupported type');
            }
        } else {
            throw new InvalidParamException('Parameter \'type\' is not set');
        }
    }

    /**
     * @param $objectId
     * @return array
     */
    public static function getFields($objectId)
    {
        $fields = [];
        $object = Object::findById($objectId);
        if ($object) {
            $fields['object'] = array_diff((new $object->object_class)->attributes(), ['id']);
            $fields['object'] = array_combine($fields['object'], $fields['object']);
            $fields['property'] = ArrayHelper::getColumn(static::getProperties($objectId), 'key');
            $fields['additionalFields'] = [];
        }
        return $fields;
    }

    /**
     * @param $objectId
     * @return array
     */
    protected static function getProperties($objectId)
    {
        $properties = [];
        $groups = PropertyGroup::getForObjectId($objectId);
        foreach ($groups as $group) {
            $props = Property::getForGroupId($group->id);
            foreach ($props as $prop) {
                $properties[] = $prop;
            }
        }
        return $properties;
    }

    /**
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!isset($config['object'])) {
            throw new InvalidParamException('Parameters \'object\' is not set');
        }
        $this->object = $config['object'];
        if (is_numeric($this->object)) {
            $this->object = Object::findById($this->object);
        } elseif (!($this->object instanceof Object)) {
            throw new InvalidParamException('Parameter "object" not Object or numeric');
        }
        unset($config['object']);
        parent::__construct($config);
    }

    /**
     * @param $objectId
     * @param $object
     * @param array $objectFields
     * @param array $properties
     * @param array $propertiesFields
     * @param array $row
     * @param array $titleFields
     * @throws \Exception
     */
    protected function save($objectId, $object, $objectFields = [], $properties = [], $propertiesFields = [], $row=[], $titleFields=[], $columnsCount = null)
    {
        if ($columnsCount === null) {
            $columnsCount = count($titleFields);
        }
        try {
            $rowFields = array_combine(array_keys($titleFields), array_slice($row, 0, $columnsCount));
        } catch(\Exception $e) {
            echo "title fields: ";
            var_dump(array_keys($titleFields));
            echo "\n\nRow:";
            var_dump($row);
            echo "\n\n";
            throw $e;
        }

        $class = $this->object->object_class;
        if ($objectId > 0) {
            /** @var ActiveRecord $objectModel */
            $objectModel = $class::findOne($objectId);
            if (!is_object($objectModel)) {
                if ($this->createIfNotExists === true) {
                    $objectModel = new $class;
                    $objectModel->id = $objectId;
                } else {
                    return;
                }
            }
            $objectData = [];
            foreach ($objectFields as $field) {
                if (isset($object[$field])) {
                    $objectData[$field] = $object[$field];
                }
            }
        } else {
            /** @var ActiveRecord $objectModel */
            $objectModel = new $class;
            $objectModel->loadDefaultValues();
            $objectData = $object;
        }
        if ($objectModel) {

            if ($objectModel instanceof ImportableInterface) {
                $objectModel->processImportBeforeSave($rowFields, $this->multipleValuesDelimiter, $this->additionalFields);
            }

            if ($objectModel->save()) {

                // add PropertyGroup to object
                if (!is_array($this->addPropertyGroups)) {
                    $this->addPropertyGroups = [];
                }
                foreach ($this->addPropertyGroups as $propertyGroupId) {
                    $model = new ObjectPropertyGroup();
                    $model->object_id = $this->object->id;
                    $model->object_model_id = $objectModel->id;
                    $model->property_group_id = $propertyGroupId;
                    $model->save();
                }
                if (count($this->addPropertyGroups) > 0) {
                    $objectModel->updatePropertyGroupsInformation();
                }

                $propertiesData = [];
                $objectModel->getPropertyGroups();

                foreach ($propertiesFields as $propertyId => $field) {
                    if (isset($properties[$field['key']])) {
                        $value = $properties[$field['key']];

                        if (isset($field['processValuesAs'])) {
                            // it is PSV in text
                            // we should convert it to ids
                            $staticValues = PropertyStaticValues::getValuesForPropertyId($propertyId);

                            $representationConversions = [
                                // from -> to
                                'text' => 'name',
                                'value' => 'value',
                                'id' => 'id',
                            ];
                            $attributeToGet = $representationConversions[$field['processValuesAs']];
                            $ids = [];
                            foreach ($value as $initial) {
                                $original = $initial;
                                $initial = mb_strtolower(trim($original));
                                $added = false;
                                foreach ($staticValues as $static) {
                                    if (mb_strtolower(trim($static[$attributeToGet])) === $initial) {
                                        $ids [] = $static['id'];
                                        $added = true;
                                    }
                                }
                                if (!$added) {
                                    // create PSV!
                                    $model = new PropertyStaticValues();
                                    $model->property_id = $propertyId;
                                    $model->name = $model->value = $model->slug = $original;
                                    $model->sort_order = 0;
                                    $model->title_append = '';
                                    if ($model->save()) {
                                        $ids[] = $model->id;
                                    }

                                    //flush cache!
                                    unset(PropertyStaticValues::$identity_map_by_property_id[$propertyId]);

                                    \yii\caching\TagDependency::invalidate(
                                        Yii::$app->cache,
                                        [
                                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(Property::className(), $propertyId)
                                        ]
                                    );
                                }
                            }
                            $value = $ids;
                        }

                        $propertiesData[$field['key']] = $value;
                    }
                }

                if (!empty($propertiesData)) {

                    $objectModel->saveProperties(
                        [
                            "Properties_{$objectModel->formName()}_{$objectModel->id}" => $propertiesData
                        ]
                    );
                }

                if ($objectModel instanceof ImportableInterface) {
                    $objectModel->processImportAfterSave($rowFields, $this->multipleValuesDelimiter, $this->additionalFields);
                }

                if ($objectModel->hasMethod('invalidateTags')) {
                    $objectModel->invalidateTags();
                }
            } else {
                throw new \Exception('Cannot save object: ' . var_export($objectModel->errors, true) . var_export($objectData, true) . var_export($objectModel->getAttributes(), true));
            }
        }
    }

    /**
     * @param array $exportFields
     * @return array
     */
    public function getAllFields($fields = [])
    {
        $result = [];

        $fields_object = isset($fields['object']) ? $fields['object'] : [];
        $fields_property = isset($fields['property']) ? $fields['property'] : [];
        $fields_additional = isset($fields['additionalFields']) ? $fields['additionalFields'] : [];

        $result['fields_object'] = $data_header = array_merge($fields_object, ['internal_id']);

        $result['fields_property'] = array_filter($fields_property, function($input) use (&$data_header) {
            if (1 == $input['enabled']) {
                $data_header[] = $input['key'];
                return true;
            }
            return false;
        });

        $result['fields_additional'] = array_filter($fields_additional, function($input) use (&$data_header) {
            if (1 == $input['enabled']) {
                $data_header[] = $input['key'];
                return true;
            }
            return false;
        });

        $result['fields_header'] = $data_header;

        return $result;
    }

    /**
     * @param $exportFields
     * @param array $conditions
     * @param int $batchSize
     * @return bool
     * @throws \Exception
     */
    public function processExport($exportFields = [], $conditions = [], $batchSize = 100)
    {
        $fields = $this->getAllFields($exportFields);

        $class = $this->object->object_class;
        /** @var $select ActiveQuery */
        $select = $class::find();

        $representationConversions = [
            'text' => 'name',
            'value' => 'value',
            'id' => 'psv_id',
        ];

        if (
            isset($conditions['category']) &&
            is_array($conditions['category']) &&
            $this->object->id == Object::getForClass(Product::className())->id
        ) {
            foreach ($conditions['category'] as $condition) {
                $joinTableName = 'Category'.$condition['value'];

                $select->innerJoin(
                    "{{%product_category}} " . $joinTableName,
                    "$joinTableName.object_model_id = product.id"
                );
                $select->andWhere(
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
                    $select->andWhere($conditionOptions);
                } elseif ($condition['comparison'] == 'OR') {
                    $select->orWhere($conditionOptions);
                }
            }
        }
        if (isset($conditions['property']) && is_array($conditions['property'])) {
            foreach ($conditions['property'] as $condition) {
                $property = Property::findById($condition['value']);

                if ($property && isset($condition['option']) &&  !empty($condition['option'])) {
                    if ($property->is_eav) {
                        $joinTableName = 'EAVJoinTable'.$property->id;

                        $select->innerJoin(
                            $this->object->eav_table_name . " " . $joinTableName,
                            "$joinTableName.object_model_id = " .
                            Yii::$app->db->quoteTableName($this->object->object_table_name) . ".id "
                        );
                        $select->andWhere(
                            new Expression(
                                '`' . $joinTableName . '`.`value` '.$condition['operators'].' "'.$condition['option'].'" AND `' .
                                $joinTableName . '`.`key` = "'. $property->key.'"'
                            )
                        );
                    } elseif ($property->has_static_values) {
                        $joinTableName = 'OSVJoinTable'.$property->id;
                        $propertyStaticValue = PropertyStaticValues::find()->where(['value'=>$condition['option']])->one();

                        if ($propertyStaticValue) {
                            $select->innerJoin(
                                ObjectStaticValues::tableName() . " " . $joinTableName,
                                "$joinTableName.object_id = " . intval($this->object->id) .
                                " AND $joinTableName.object_model_id = " .
                                Yii::$app->db->quoteTableName($this->object->object_table_name) . ".id "
                            );

                            $select->andWhere(
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

        $data = [];
        $batchSize = intval($batchSize) <= 0 ? 100 : intval($batchSize);
        foreach ($select->each($batchSize) as $object) {
            $row = [];

            foreach ($fields['fields_object'] as $field) {
                if ('internal_id' === $field) {
                    $row[] = $object->id;
                } else {
                    $row[] = isset($object->$field) ? $object->$field : '';
                }
            }

            foreach ($fields['fields_property'] as $field_id => $field) {
                $value = $object->getPropertyValuesByPropertyId($field_id);

                if (!is_object($value)) {
                    $value = '';
                } elseif (count($value->values) > 1 && isset($fields_property[$field_id])) {
                    if (isset($fields_property[$field_id]['processValuesAs'])) {
                        $attributeToGet = $representationConversions[$fields_property[$field_id]['processValuesAs']];
                        $newValues = [];
                        foreach ($value->values as $val) {
                            $newValues[] = $val[$attributeToGet];
                        }
                        $value = implode($this->multipleValuesDelimiter, $newValues);
                    }
                } else {
                    $value = (string) $value;
                }

                $row[] = $value;
            }

            if (!empty($fields['fields_additional']) && $object->hasMethod('getAdditionalFields')) {
                $fieldsFromModel = $object->getAdditionalFields($fields['fields_additional']);
                foreach ($fields['fields_additional'] as $key => $configuration) {
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

            $data[] = $row;
        }

        unset($value, $row, $object, $select, $class);

        return $this->getData($fields['fields_header'], $data);
    }

    /**
     * @param array $importFields
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function processImport($importFields = [])
    {
        $fields = $this->getAllFields($importFields);
        $data = $this->setData();

        $objectFields = static::getFields($this->object->id);
        $objAttributes = $objectFields['object'];
        $propAttributes = isset($objectFields['property']) ? $objectFields['property'] : [];

        $titleFields = array_filter(
            array_shift($data),
            function ($value) {
                return !empty($value);
            }
        );
        $titleFields = array_intersect_key(array_flip($titleFields), array_flip($fields['fields_header']));

        $transaction = \Yii::$app->db->beginTransaction();
        $columnsCount = count($titleFields);
        try {
            foreach ($data as $row) {
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
                        foreach ($values as $value) {
                            $value = trim($value);
                            if (!empty($value)) {
                                $propValue[] = $value;
                            }
                        }
                    }
                    $propData[$attribute] = $propValue;
                }

                $objectId = isset($titleFields['internal_id']) ? $row[$titleFields['internal_id']] : 0;
                $this->save(
                    $objectId,
                    $objData,
                    $fields['fields_object'],
                    $propData,
                    $fields['fields_property'],
                    $row,
                    $titleFields,
                    $columnsCount
                );
            }
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return true;
    }
}
