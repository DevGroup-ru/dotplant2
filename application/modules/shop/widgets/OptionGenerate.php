<?php

namespace app\modules\shop\widgets;

use app\models\Object;
use app\models\Property;
use app\models\PropertyGroup;
use kartik\helpers\Html;
use kartik\icons\Icon;
use Yii;
use yii\base\Widget;
use yii\db\Query;
use yii\helpers\Json;
use yii\helpers\Url;

class OptionGenerate extends Widget
{
    public $viewFile = 'OptionGenerate';
    public $genButton;
    public $addButton;

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
        $parent_id = $this->model->main_category_id;
        $owner_id = $this->model->id;
        $this->addButton = Html::a(
            Icon::show('plus') . Yii::t('app', 'Add'),
            Url::toRoute(['/shop/backend-product/edit',
                'parent_id' => $parent_id,
                'owner_id' => $owner_id,
                'returnUrl' => \app\backend\components\Helper::getReturnUrl(),
            ]),
            ['class' => 'btn btn-success', 'id' => 'btn-add']
        );

        if (!empty($this->footer)) {
            $this->footer = Html::tag(
                'div',
                $this->addButton.' '.$this->genButton,
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
        if (null === PropertyGroup::findOne($optionGenerate['group'])) {
            $this->model->option_generate = $optionGenerate = null;
        }
        $groupModel = null;
        if (isset($optionGenerate['group'])) {
            $groupModel = PropertyGroup::findOne($optionGenerate['group']);
            $properties = Property::getForGroupId($optionGenerate['group']);
        } else {
            $group_ids = array_keys($this->property_groups_to_add);
            $group_id = array_shift($group_ids);
            $groupModel = PropertyGroup::findOne($group_id);
            $properties = Property::getForGroupId($group_id);
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
