<?php

namespace app\backend\models;

use Yii;
use yii\base\Model;
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
    public $addPropertyGroups=[];
    public $createIfNotExists = false;

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
            [['addPropertyGroups'], 'safe'],
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
        return serialize([
            'object' => $this->object,
            'fields' => $this->fields,
            'type' => $this->type,
            'user' => Yii::$app->user->id,
            'addPropertyGroups' => is_array($this->addPropertyGroups)?$this->addPropertyGroups:[],
            'createIfNotExists' => $this->createIfNotExists,
        ]);
    }

    public function unserialize($serialized)
    {
        $fields = unserialize($serialized);

        $this->object = $fields['object'];
        $this->fields = $fields['fields'];
        $this->type = $fields['type'];
        $this->user = $fields['user'];
        $this->addPropertyGroups = $fields['addPropertyGroups'];
        $this->createIfNotExists = $fields['createIfNotExists'];
    }
}
