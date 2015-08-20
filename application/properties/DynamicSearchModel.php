<?php

namespace app\properties;


use app\models\Object;
use app\models\Property;
use app\models\PropertyStaticValues;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class DynamicSearchModel extends DynamicModel
{
    /** @var \yii\db\ActiveRecord */
    private $baseModel = null;
    private $propertyGroups = null;
    /**
     * @param \yii\db\ActiveRecord $baseModel
     * @param array $config
     */
    public function __construct($baseModel, $propertyGroups, $config=[])
    {
        $this->baseModel = $baseModel;
        $this->propertyGroups = $propertyGroups;

        $attributes = $this->baseModel->activeAttributes();
        foreach ($this->propertyGroups as $groupId => $properties) {
            $attributes = ArrayHelper::merge($attributes, array_keys($properties));
        }
        parent::__construct($attributes, $config);

        $this->addRule($attributes, 'safe');

    }

    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = $this->baseModel->find();

        $table_inheritance_joined = false;

        $dataProvider = new ActiveDataProvider(
            [
                'query' => &$query,
                'pagination' => [
                    'pageSize' => 10,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_DESC
                    ]
                ],
            ]
        );

        if (!($this->load($params))) {
            return $dataProvider;
        }

        $object = Object::getForClass($this->baseModel->className());
        $baseModelTableName = $this->baseModel->tableName();

        $eavJoinsCount = 0;
        $osvJoinsCount = 0;

        foreach ($this->propertyGroups as $groupId => $properties) {
            foreach ($properties as $key => $propertyValue) {
                /** @var \app\properties\PropertyValue $propertyValue */
                $prop = Property::findById($propertyValue->property_id);
                if (empty($this->{$prop->key}) === true && $this->{$prop->key} !== '0') {
                    continue;
                }

                // determine property storage type and join needed table if needed
                if ($prop->is_column_type_stored) {
                    if ($table_inheritance_joined === false) {
                        $table_inheritance_joined = true;
                        $query->join('INNER JOIN', $object->column_properties_table_name . ' ti',
                            'ti.object_model_id = ' . $baseModelTableName . '.id');
                    }

                    if ($prop->value_type === 'STRING' && $prop->property_handler_id !== 3) {
                        $query->andFilterWhere(['like', 'ti.'.$prop->key, $this->{$prop->key}]);
                    } else {
                        $query->andFilterWhere(['ti.'.$prop->key => $this->{$prop->key}]);
                    }

                } elseif ($prop->is_eav) {
                    $eavJoinsCount++;
                    $eavTableName = 'eav'.$eavJoinsCount;

                    $key = 'key'.$eavJoinsCount;

                    $query->join(
                        'INNER JOIN',
                        "$object->eav_table_name $eavTableName",
                        $eavTableName.'.object_model_id = '.$baseModelTableName.".id AND $eavTableName.key=:$key",
                        [$key=>$prop->key]
                    );
                    if ($prop->value_type === 'STRING' && $prop->property_handler_id !== 3) {
                        $query->andFilterWhere(['like', $eavTableName.'.value', $this->{$prop->key}]);
                    } else {
                        // numeric - direct match
                        $query->andFilterWhere([$eavTableName.'.value' => $this->{$prop->key}]);

                    }

                } elseif ($prop->has_static_values) {
                    $osvJoinsCount++;
                    $osvTableName = 'osv'.$osvJoinsCount;

                    $query->join(
                        'INNER JOIN',
                        "object_static_values $osvTableName",
                        "$osvTableName.object_id={$object->id} AND $osvTableName.object_model_id=$baseModelTableName.id"
                    );


                    // numeric - direct match
                    $query->andFilterWhere(["$osvTableName.property_static_value_id", $this->{$prop->key}]);

                }
            }
        }

        return $dataProvider;
    }


    public function columns($baseModelColumns)
    {
        $columns = $baseModelColumns;
        foreach ($this->propertyGroups as $groupId => $properties) {
            foreach ($properties as $key => $propertyValue){
                /** @var PropertyValue $propertyValue */
                $prop = Property::findById($propertyValue->property_id);

                if ($prop->has_static_values) {
                    $column = [
                        'value' => function($model) use($prop){
                            return $model->property($prop->key);
                        },
                        'filter' => PropertyStaticValues::getSelectForPropertyId($propertyValue->property_id),
                    ];
                } elseif ($prop->property_handler_id === 3){
                    $column = [
                        'value' => function($model) use($prop){
                            return $model->property($prop->key);
                        },
                        'filter' => [
                            0 => Yii::t('app', 'No'),
                            1 => Yii::t('app', 'Yes'),
                        ],
                    ];
                } else {
                    $column = [
                        'value' => function($model) use($prop){
                            return $model->property($prop->key);
                        },
                    ];
                }
                $column = ArrayHelper::merge($column, [
                    'header' => $prop->name,
                    'attribute' => $prop->key,
                    'enableSorting' => true,
                ]);
                $columns[] = $column;
            }
        }
        return $columns;
    }
}