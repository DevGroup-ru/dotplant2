<?php

namespace app\components\filters;

use app\components\filters\FilterQueryInterface;
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
                    $query->innerJoin('object_static_values as osvf'.$propertyId, 'product.id=osvf'.$propertyId.'.object_model_id');
                    $query->innerJoin(
                        'property_static_values as psvf'.$propertyId,
                        'psvf'.$propertyId.'.id=osvf'.$propertyId.'.property_static_value_id'
                    );

                    $query->andWhere('psvf'.$propertyId.'.value >= :minData ')
                        ->andWhere('psvf'.$propertyId.'.value <= :maxData ')
                        ->andWhere(
                            [
                                'psvf'.$propertyId.'.property_id' => $propertyId,
                            ]
                        )->addParams([
                            ':minData' => (int)$get[$this->minValueAttribute][$propertyId],
                            ':maxData' => (int)$get[$this->maxValueAttribute][$propertyId],
                        ]);

                    $cacheKeyAppend .= 'FilterRangeProperty[min:' . (int)$get[$this->minValueAttribute][$propertyId]
                        . ':max' . (int)$get[$this->maxValueAttribute][$propertyId] . ']';
                }
            }
        }


        return $query;


    }

} 