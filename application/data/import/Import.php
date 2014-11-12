<?php

namespace app\data\import;

use app\models\Object;
use app\models\Property;
use app\models\PropertyGroup;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

abstract class Import extends Component
{
    protected $object;
    protected $properties = null;
    public $file;

    abstract public function getData($importFields);
    abstract public function setData($exportFields);

    /**
     * @param $object
     * @param $type
     * @param null $file
     * @return ImportCsv
     * @throws \Exception
     */
    public static function createInstance($object, $type, $file = null)
    {
        switch ($type) {
            case 'csv':
                return new ImportCsv(['object' => $object, 'file' => $file]);

            default:
                throw new \Exception('Unsupported type');
        }
    }

    public static function getFields($objectId)
    {
        $fields = [];
        $object = Object::findById($objectId);
        if ($object) {
            $fields['object'] = array_diff((new $object->object_class)->attributes(), ['id']);
            $fields['property'] = ArrayHelper::getColumn(static::getProperties($objectId), 'key');
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
            throw new InvalidParamException('Parameters "file" and "object" must be set');
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

    protected function save($objectId, $object, $objectFields = [], $properties = [], $propertiesFields = [])
    {
        $class = $this->object->object_class;
        if ($objectId > 0) {
            /** @var ActiveRecord $objectModel */
            $objectModel = $class::findById($objectId);
            $objectData = [];
            foreach ($objectFields as $field) {
                if (isset($object[$field])) {
                    $objectData[$field] = $object[$field];
                }
            }
        } else {
            /** @var ActiveRecord $objectModel */
            $objectModel = new $class;
            $objectData = $object;
        }
        if ($objectModel) {
            $objectModel->load([$objectModel->formName() => $objectData]);
            if ($objectModel->save()) {
                $propertiesData = [];
                foreach ($propertiesFields as $field) {
                    if (isset($properties[$field])) {
                        $propertiesData[$field] = $properties[$field];
                    }
                }
                if (!empty($propertiesData)) {
                    $objectModel->saveProperties(
                        [
                            "Properties_{$objectModel->formName()}_{$objectModel->id}" => $propertiesData
                        ]
                    );
                }
            } else {
                throw new \Exception('Cannot save object');
            }
        }
    }
}
