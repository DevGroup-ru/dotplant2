<?php

namespace app\backend\widgets;

use app\modules\shop\models\Category;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

class JSSelectableTree extends JSTree
{
    public $fieldName = 'FieldName';
    public $flagFieldName = 'FlagFieldName';
    public $multiple = true;
    public $selectedItems = [];
    public $selectLabel = '';
    public $selectLabelOptions = ['class' => 'control-label'];
    public $selectOptions = [];
    public $selectParents = true;
    public $stateKey = 'default';

    public function run()
    {
        $id = $this->getId();
        $this->plugins = ArrayHelper::merge($this->plugins, ['checkbox']);
        $items =[];
        if (empty($this->selectedItems)) {
            $parent = Category::findById(Yii::$app->request->get('parent_id'));
            while ($parent instanceof Category) {
                $this->selectedItems[] = $parent->id;
                $parent = $parent->parent;
            }
        }
        $this->routes['getTree'] = ArrayHelper::merge(
            $this->routes['getTree'],
            ['selectedItems' => implode(',', $this->selectedItems)]
        );
        if (isset($this->routes['edit'])) {
            $items['edit'] = [
                'label' => Yii::t('app', 'Edit'),
                'icon' => 'fa fa-pencil',
                'action' => new JsExpression(
                    "function (a) {
                        var \$object = $(a.reference[0]);
                        document.location = " . Json::encode(Url::to($this->routes['edit'])) . "
                            + '?id=' + \$object.attr('data-id') + '&parent_id=' +
                            \$object.attr('data-parent-id');
                        }"
                ),
            ];
        }
        if (isset($this->routes['open'])) {
            $items['open'] = [
                'label' => Yii::t('app', 'Open'),
                'icon' => 'fa fa-folder-open',
                'action' => new JsExpression(
                    "function (a) {
                        var \$object = $(a.reference[0]);
                        document.location = " . Json::encode(Url::to($this->routes['open'])) . "
                            + '?parent_id=' + \$object.attr('data-id');
                        }"
                ),
            ];
        }
        if (isset($this->routes['create'])) {
            $items['create'] = [
                'label' => Yii::t('app', 'Create'),
                'icon' => 'fa fa-plus-circle',
                'action' => new JsExpression(
                    "function (a) {
                        var \$object = $(a.reference[0]);
                        document.location = " . Json::encode(Url::to($this->routes['create'])) . "
                            + '?parent_id=' + \$object.attr('data-id');
                        }"
                ),
            ];
        }
        if (isset($this->routes['delete'])) {
            $items['delete'] = [
                'label' => Yii::t('app', 'Delete'),
                'icon' => 'fa fa-trash-o',
                'action' => new JsExpression(
                    "function (a) {
                        var \$object = $(a.reference[0]);
                        document.location = " . Json::encode(Url::to($this->routes['delete'])) . "
                            + '?id=' + \$object.attr('data-id');
                        }"
                ),
            ];
        }

        $options = [
            'state' => [
                'key' => $this->stateKey,
            ],
            'plugins' => $this->plugins,
            'core' => [
                'check_callback' => true,
                'data' => [
                    'url' => new JsExpression(
                        "function (node) {
                            return ".Json::encode(Url::to($this->routes['getTree'])).";
                        }"
                    ),
                    'data' => new JsExpression(
                        "function (node) {
                            return { 'id' : node.id };
                        }"
                    ),
                ],
                'multiple' => $this->multiple,
            ],
            'checkbox' => [
                'three_state' => false,
            ],
            'contextmenu' => [
                'items' => $items,
            ],
            'dnd' => [
                'is_draggable' => false,
            ],
        ];
        if (count($this->types) > 0) {
            $options['types'] = $this->types;
        }

        $this->selectOptions['id'] = $id . '-select';

        return $this->render(
            'JSSelectableTree',
            [
                'id' => $id,
                'flagFieldName' => $this->flagFieldName,
                'fieldName' => $this->fieldName,
                'model' => $this->model,
                'multiple' => $this->multiple,
                'options' => Json::encode($options),
                'routes' => $this->routes,
                'selectedItems' => $this->selectedItems,
                'selectLabel' => $this->selectLabel,
                'selectLabelOptions' => $this->selectLabelOptions,
                'selectOptions' => $this->selectOptions,
                'selectParents' => $this->selectParents,
            ]
        );
    }
}
