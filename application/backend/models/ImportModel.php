<?php

namespace app\backend\models;

use yii\base\Model;
use yii\web\UploadedFile;

class ImportModel extends Model
{
    public $object;
    /**
     * @var UploadedFile|null file attribute
     */
    public $file;
    public $fields;
    public $type;

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
        ];
    }
}
