<?php

namespace app\backend\widgets;


use app\models\Property;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use app\models\PropertyGroup;
use yii\base\Widget;
use yii\db\ActiveRecord;

/**
 * @property \app\models\Object  $object
 * @property ActiveRecord  $model
 */
class DataRelationsWidget extends Widget
{
    public $object = null;
    /**
     * @var array, e.g.
     * [
     * [
     * 'key' => 'key',
     * 'label' => 'label',
     * 'required' => true
     * ]
     * ]
     */
    public $fields = [];
    /**
     * @var array, e.g.
     * [
     * [
     * 'class' => \app\modules\image\models\Image::className(),
     * 'relationName' => 'getImages'
     * ],
     * ]
     */
    public $relations = [];

    public $data = [];

    public $types = [
        'field' => 'Field type',
        'property' => 'Property type',
        'relation' => 'Relation type'
    ];

    public $viewFile = 'dataRelations';

    protected $model = null;

    public function init()
    {

        $class = $this->object->object_class;

        $this->model = new $class;


        return parent::init();
    }

    public function run()
    {
        echo $this->render(
            $this->viewFile,
            [
                'widgetId' => $this->id,
                'object' => $this->object,
                'fields' => $this->fields,
                'types' => $this->types,
                'data' => $this->data,
                'options' => $this->getOptions()
            ]
        );
    }


    protected function getOptions()
    {
        return [
            'fields' => $this->getFields(),
            'properties' => $this->getProperties(),
            'relations' => $this->getRelations()
        ];
    }


    protected function getRelations()
    {
        $results = [];
        foreach ($this->relations as $link) {
            $results[$link['relationName']] = ArrayHelper::merge(
                $link,
                [
                    'values' => (new $link['class'])->attributeLabels()
                ]
            );;
        }

        return $results;

    }


    protected function getFields()
    {
        return $this->model->attributeLabels();
    }

    protected function getProperties()
    {
        $query = new Query;
        $query->select(
            Property::tableName() . '.key, ' . Property::tableName() . '.name'
        )
            ->from(Property::tableName());
        $query->innerJoin(
            PropertyGroup::tableName(),
            PropertyGroup::tableName() . '.id = ' . Property::tableName() . '.property_group_id'
        );
        $query->andWhere(
            [
                PropertyGroup::tableName() . '.object_id' => $this->object->id
            ]
        );
        $command = $query->createCommand();

        return ArrayHelper::map($command->queryAll(), 'key', 'name');
    }

}