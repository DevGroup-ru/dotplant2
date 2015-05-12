<?php

namespace app\modules\data\models;

use app\modules\data\DataModule;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\UploadedFile;

class ImportModel extends Model implements \Serializable
{
    public $object;
    private $user = 0;
    /**
     * @var UploadedFile|null file attribute
     */
    public $file;
    public $fields;
    public $type;

    public $filterByCategory;
    public $filterByProperties;
    public $filterByFields;

    public $conditions = [];

    /**
     * Array of PropertyGroup's ids to add to each record
     * @var array
     */
    public $addPropertyGroups = [];

    /**
     * Should we create new record if supplied 'internal_id' doesn't exist
     * @var bool
     */
    public $createIfNotExists = false;

    /**
     * Delimiter for multiple values of field which were supplied in one field
     * Can be regexp starting with a slash - then preg_split used
     * @var string
     */
    public $multipleValuesDelimiter = '|';

    /**
     * Array of additional fields to process
     * (see ExportableInterface::exportableAdditionalFields for format)
     * @var array
     */
    public $additionalFields = [];


    public function getFilename($prefix = '')
    {
        if (trim($prefix)) {
            $prefix = "{$prefix}_";
        } else {
            $prefix = '';
        }
        return "{$prefix}{$this->object}_{$this->getUser()}." . $this->getExtension($this->type);
    }

    public function getUser()
    {
        if ($this->user <= 0) {
            $this->user = Yii::$app->user->id;
        }

        return $this->user;
    }

    public function load($data, $formName = null)
    {
        if (isset($data['ImportModel']) &&
            isset($data['ImportModel']['fields']) &&
            isset($data['ImportModel']['fields']['property']) &&
            $data['ImportModel']['fields']['property']
        ) {
            foreach ($data['ImportModel']['fields']['property'] as $key => $property) {
                if (!isset($property['enabled']) || !$property['enabled']) {
                    unset($data['ImportModel']['fields']['property'][$key]);
                }
            }
        }
        if (isset($data['ImportModel']) &&
            isset($data['ImportModel']['fields']) &&
            isset($data['ImportModel']['fields']['additionalFields']) &&
            $data['ImportModel']['fields']['additionalFields']
        ) {
            foreach ($data['ImportModel']['fields']['additionalFields'] as $key => $additionalFields) {
                if (!isset($additionalFields['enabled']) || !$additionalFields['enabled']) {
                    unset($data['ImportModel']['fields']['additionalFields'][$key]);
                }
            }
        }
        return parent::load($data, $formName = null);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'extensions' => 'csv, xls, xlsx'],
            [['object'], 'integer'],
            [['object'], 'required'],
            [['type'] ,'in', 'range'=> array_keys(self::knownTypes())],
            [['type'] ,'default', 'value'=> Yii::$app->modules['data']->defaultType ],
            [['fields', 'conditions' ,'type'], 'safe'],
            [['addPropertyGroups', 'multipleValuesDelimiter', 'additionalFields'], 'safe'],
            [['createIfNotExists'], 'boolean'],
        ];
    }

    public static function knownTypes()
    {
        return [
            'csv' => 'CSV',
            'excelCsv' => 'Excel CSV',
            'xls' => 'Excel XLS',
            'xlsx' => 'Excel XLSX',
        ];
    }

    protected function getExtension($type)
    {
        $extensions = [
            'excelCsv' => 'csv',
            'csv' => 'csv',
            'xls' => 'xls',
            'xlsx' => 'xlsx',
        ];
        if (!isset($extensions[$type])) {
            return 'unknown';
        }
        return $extensions[$type];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => \Yii::t('app', 'File'),
            'object' => \Yii::t('app', 'Object'),
            'fields' => \Yii::t('app', 'Fields'),
            'type' => \Yii::t('app', 'Type'),
            'createIfNotExists' => \Yii::t('app', 'Create record with supplied internal_id as ID if not exists.'),
            'multipleValuesDelimiter' => \Yii::t('app', 'Multiple values delimiter'),
        ];
    }

    public function serialize()
    {
        return Json::encode([
            'object' => $this->object,
            'fields' => $this->fields,
            'conditions' => $this->conditions,
            'type' => $this->type,
            'user' => Yii::$app->user->id,
            'addPropertyGroups' => is_array($this->addPropertyGroups) ? $this->addPropertyGroups : [],
            'createIfNotExists' => $this->createIfNotExists,
            'multipleValuesDelimiter' => $this->multipleValuesDelimiter,
            'additionalFields' => $this->additionalFields,
        ]);
    }

    public function unserialize($serialized)
    {
        $fields = Json::decode($serialized);

        $this->object = $fields['object'];
        $this->fields = $fields['fields'];
        $this->conditions = isset($fields['conditions']) ? $fields['conditions'] : [];
        $this->type = $fields['type'];
        $this->user = $fields['user'];
        $this->addPropertyGroups = $fields['addPropertyGroups'];
        $this->createIfNotExists = $fields['createIfNotExists'];
        $this->multipleValuesDelimiter = $fields['multipleValuesDelimiter'];
        $this->additionalFields = isset($fields['fields']['additionalFields']) ? $fields['fields']['additionalFields'] : [];

    }
}
