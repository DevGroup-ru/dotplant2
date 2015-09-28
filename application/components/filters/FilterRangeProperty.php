<?php

namespace app\components\filters;

use app\components\filters\FilterQueryInterface;
use app\models\Property;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use Yii;

class FilterRangeProperty implements FilterQueryInterface
{


    public $changeAttribute = 'changeValue';
    public $minValueAttribute = 'minValue';
    public $maxValueAttribute = 'maxValue';

    public function filter(ActiveQuery $query, &$cacheKeyAppend)
    {

        $get = Yii::$app->request->post();
        if (isset($get['changeValue']) && is_array($get['changeValue'])) {
            foreach ($get['changeValue'] as $propertyId => $isActive) {
                if ($isActive
                    && isset($get[$this->minValueAttribute][$propertyId])
                    && isset($get[$this->maxValueAttribute][$propertyId])
                    && is_numeric($get[$this->minValueAttribute][$propertyId])
                    && is_numeric($get[$this->maxValueAttribute][$propertyId])
                ) {
                    $property = Property::findById($propertyId);
                    if ($property->has_static_values) {
                        $query->innerJoin('{{%object_static_values}} as osvf' . $propertyId,
                            '{{%product}}.id=osvf' . $propertyId . '.object_model_id');
                        $query->innerJoin(
                            '{{%property_static_values}} as psvf' . $propertyId,
                            'psvf' . $propertyId . '.id=osvf' . $propertyId . '.property_static_value_id'
                        );
                        $query->andWhere('psvf' . $propertyId . '.value >= :minData ')
                            ->andWhere('psvf' . $propertyId . '.value <= :maxData ')
                            ->andWhere(
                                [
                                    'psvf' . $propertyId . '.property_id' => $propertyId,
                                ]
                            )->addParams([
                                ':minData' => (int)$get[$this->minValueAttribute][$propertyId],
                                ':maxData' => (int)$get[$this->maxValueAttribute][$propertyId],
                            ]);
                    } elseif ($property->is_eav) {
                        $query->innerJoin(
                            '{{%product_eav}} as peav' . $propertyId,
                            '{{%product}}.id=peav' . $propertyId . '.object_model_id'
                        );
                        $query->andWhere(
                            [
                                'peav' . $propertyId . '.property_group_id' => $property->property_group_id,
                                'peav' . $propertyId . '.key' => $property->key,
                            ]
                        );
                        $query->andWhere(
                            [
                                '>=',
                                'peav' . $propertyId . '.value',
                                (int)$get[$this->minValueAttribute][$propertyId]

                            ]
                        );
                        $query->andWhere(
                            [
                                '<=',
                                'peav' . $propertyId . '.value',
                                (int)$get[$this->maxValueAttribute][$propertyId]

                            ]
                        );
                    }
                    $cacheKeyAppend .= 'FilterRangeProperty:propertyId' . $propertyId . ':[min:' . (int)$get[$this->minValueAttribute][$propertyId]
                        . ':max' . (int)$get[$this->maxValueAttribute][$propertyId] . ']';
                }
            }
        }
        return $query;
    }

} 