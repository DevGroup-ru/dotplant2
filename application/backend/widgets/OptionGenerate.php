<?php

namespace app\backend\widgets;

use app\models\Object;
use app\models\Property;
use app\models\PropertyGroup;
use kartik\helpers\Html;
use kartik\icons\Icon;
use Yii;
use yii\base\Widget;
use yii\db\Query;
use yii\helpers\Json;

class OptionGenerate extends Widget
{
    public $viewFile = 'OptionGenerate';
    public $genButton;

    public $model;
    public $form;
    public $property_groups_to_add;
    public $object;
    public $footer;

    public function run()
    {
        $this->genButton = Html::a(
            Icon::show('edit') . Yii::t('app', 'Generate'),
            '#',
            ['class' => 'btn btn-success', 'id' => 'btn-generate']
        );

        if (!empty($this->footer)) {
            $this->footer = Html::tag(
                'div',
                $this->genButton,
                ['class'=>'widget-footer']
            );
        }

        $this->object = Object::getForClass(get_class($this->model));

        $rest_pg = (new Query())
            ->select('id, name')
            ->from(PropertyGroup::tableName())
            ->where(
                ['object_id' => $this->object->id]
            )
            ->orderBy('sort_order')
            ->all();

        $this->property_groups_to_add = [];
        foreach ($rest_pg as $row) {
            $this->property_groups_to_add[$row['id']] = $row['name'];
        }

        $optionGenerate = Json::decode($this->model->option_generate);

        $groupModel = null;
        if (isset($optionGenerate['group'])) {
            $groupModel = PropertyGroup::findOne($optionGenerate['group']);
            $properties = Property::getForGroupId($optionGenerate['group']);
        } else {
            $properties = [];
        }
        if (is_null($groupModel)) {
            $groupModel = new PropertyGroup();
        }

        return $this->render(
            $this->viewFile,
            [
                'model' => $this->model,
                'form' => $this->form,
                'groups' => $this->property_groups_to_add,
                'groupModel' => $groupModel,
                'properties' => $properties,
                'optionGenerate' => $optionGenerate,
                'footer' => $this->footer,
            ]
        );
    }
}
