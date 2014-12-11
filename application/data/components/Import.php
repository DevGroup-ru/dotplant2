<?php

namespace app\data\components;

use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
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

    abstract public function getData($importFields);
    abstract public function setData($exportFields);

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

                default:
                    throw new \Exception('Unsupported type');
            }
        } else {
            throw new InvalidParamException('Parameter \'type\' is not set');
        }
    }

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

    public function getObject()
    {
        return $this->object;
    }

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

    protected function save($objectId, $object, $objectFields = [], $properties = [], $propertiesFields = [], $row=[], $titleFields=[])
    {

        $rowFields = array_combine(array_keys($titleFields), $row);

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
            $objectModel->load([$objectModel->formName() => $objectData]);

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
}
