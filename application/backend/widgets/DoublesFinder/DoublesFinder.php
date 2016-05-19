<?php

namespace app\backend\widgets\DoublesFinder;

use app\models\Property;
use app\models\PropertyStaticValues;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Widget;
use yii\caching\TagDependency;

class DoublesFinder extends Widget
{
    public function run()
    {
        $psvDoubledSlugs = \Yii::$app->cache->get('psvDoubledSlugs:data');
        if ($psvDoubledSlugs === false) {
            $psvDoubledSlugs = PropertyStaticValues::find()
                ->select(['slug'])
                ->groupBy('slug')
                ->having('COUNT(*) > 1')
                ->indexBy('slug')
                ->column();
            if (count($psvDoubledSlugs) > 0) {
                foreach ($psvDoubledSlugs as $psvSlug) {
                    $psvDoubledSlugs[$psvSlug] = Property::find()
                        ->select(['id', 'name', 'property_group_id'])
                        ->asArray(true)
                        ->where(
                            [
                                'id' => PropertyStaticValues::find()
                                    ->select('property_id')
                                    ->where(['slug' => $psvSlug])
                                    ->column()
                            ]
                        )
                        ->all();;
                }
                \Yii::$app->cache->set(
                    'psvDoubledSlugs',
                    $psvDoubledSlugs,
                    86400,
                    new TagDependency(
                        [
                            'tags' => ActiveRecordHelper::getCommonTag(PropertyStaticValues::class)
                        ]
                    )
                );
            }
        }
        if (count($psvDoubledSlugs) > 0) {
            echo $this->render(
                'doubles-finder',
                [
                    'psvDoubledSlugs' => $psvDoubledSlugs
                ]
            );
        }
    }
}
