<?php

namespace app\properties;

use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class PropertiesWidget
 * @property ActiveRecord $model
 * @property ActiveForm $form
 * @property Object $object
 * @property array $objectPropertyGroups
 * @property array $propertyGroupsToAdd
 * @property string $viewFile
 * @package app\properties
 */
class PropertiesWidget extends Widget
{
    private $object;
    private $objectPropertyGroups = [];
    private $propertyGroupsToAdd = [];
    public $form;
    public $model;
    public $viewFile = 'properties-widget';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->object = Object::getForClass(get_class($this->model));
        $cacheKey = 'PropertiesWidget: ' . get_class($this->model) . ':' . $this->model->id;
        $data = Yii::$app->cache->get($cacheKey);
        if ($data === false) {
            $this->objectPropertyGroups = ObjectPropertyGroup::getForModel($this->model);
            $addedPropertyGroupsIds = [];
            foreach ($this->objectPropertyGroups as $opg) {
                $addedPropertyGroupsIds[] = $opg->property_group_id;
            }
            $restPg = (new Query())
                ->select('id, name')
                ->from(PropertyGroup::tableName())
                ->where(
                    [
                        'object_id' => $this->object->id,
                    ]
                )->andWhere(
                    [
                        'not in', 'id', $addedPropertyGroupsIds,
                    ]
                )->orderBy('sort_order')
                ->all();
            $this->propertyGroupsToAdd = ArrayHelper::map($restPg, 'id', 'name');
            Yii::$app->cache->set(
                $cacheKey,
                [
                    'objectPropertyGroups' => $this->objectPropertyGroups,
                    'propertyGroupsToAdd' => $this->propertyGroupsToAdd,
                ],
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(get_class($this->model)),
                            ActiveRecordHelper::getCommonTag(PropertyGroup::className()),
                            ActiveRecordHelper::getCommonTag(Property::className()),
                        ],
                    ]
                )
            );
        } else {
            $this->objectPropertyGroups = $data['objectPropertyGroups'];
            $this->propertyGroupsToAdd = $data['propertyGroupsToAdd'];
        }
        return $this->render(
            $this->viewFile,
            [
                'model' => $this->model,
                'object' => $this->object,
                'object_property_groups' => $this->objectPropertyGroups,
                'property_groups_to_add' => $this->propertyGroupsToAdd,
                'form' => $this->form,
                'widget_id' => $this->getId(),
            ]
        );
    }
}
