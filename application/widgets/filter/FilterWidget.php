<?php

namespace app\widgets\filter;

use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use Yii;
use yii\base\Widget;

class FilterWidget extends Widget
{
    private $possible_selections = null;
    public $category_group_id = 0;
    public $current_selections = [];
    public $go_back_alignment = 'left';
    public $object_id = null;
    public $route = '/product/list';
    public $title = 'Filter';
    public $viewFile = 'filterWidget';

    public function run()
    {
        $view = $this->getView();
        FilterWidgetAsset::register($view);
        $view->registerJs(
            "jQuery('#{$this->id}').getFilters();"
        );
        $this->getPossibleSelections();
        return $this->render(
            $this->viewFile,
            [
                'id' => $this->id,
                'current_selections' => $this->current_selections,
                'possible_selections' => $this->possible_selections,
                'object_id' => $this->object_id,
                'title' => $this->title,
                'go_back_alignment' => $this->go_back_alignment,
                'route' => $this->route,
                'category_group_id' => $this->category_group_id,
            ]
        );
    }

    public function getPossibleSelections()
    {
        $this->possible_selections = [];
        $groups = PropertyGroup::getForObjectId($this->object_id);
        foreach ($groups as $group) {
            if ($group->is_internal) {
                continue;
            }
            $this->possible_selections[$group->id] = [
                'group' => $group,
                'selections' => [],
            ];
            /** @var Property[] $props */
            $props = Property::getForGroupId($group->id);
            foreach ($props as $p) {
                if ($p->has_static_values) {
                    $this->possible_selections[$group->id]['static_selections'][$p->id]
                        = PropertyStaticValues::getValuesForPropertyId($p->id);
                } elseif ($p->is_column_type_stored && $p->value_type == 'NUMBER') {
                    $this->possible_selections[$group->id]['dynamic_selections'][] = $p->id;
                }
            }
            if (count($this->possible_selections[$group->id]) === 0) {
                unset($this->possible_selections[$group->id]);
            }
        }
    }
}
