<?php

namespace app\data\models;

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
        return "{$prefix}{$this->object}_{$this->getUser()}.{$this->type}";
    }

    public function getUser()
    {
        if ($this->user <= 0) {
            $this->user = Yii::$app->user->id;
        }

        return $this->user;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'extensions' => 'csv'],
            [['object'], 'integer'],
            [['object'], 'required'],
            [['fields', 'type'], 'safe'],
            [['addPropertyGroups', 'multipleValuesDelimiter', 'additionalFields'], 'safe'],
            [['createIfNotExists'], 'boolean'],
        ];
    }

    public static function knownTypes()
    {
        return [
            'csv' => 'CSV',
        ];
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
        ];
    }

    public function serialize()
    {
        return Json::encode([
            'object' => $this->object,
            'fields' => $this->fields,
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
        $this->type = $fields['type'];
        $this->user = $fields['user'];
        $this->addPropertyGroups = $fields['addPropertyGroups'];
        $this->createIfNotExists = $fields['createIfNotExists'];
        $this->multipleValuesDelimiter = $fields['multipleValuesDelimiter'];
        $this->additionalFields = isset($fields['fields']['additionalFields'])?$fields['fields']['additionalFields']:[];

    }
}
