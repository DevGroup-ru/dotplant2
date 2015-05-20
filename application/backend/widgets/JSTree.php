<?php

namespace app\backend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

class JSTree extends Widget
{

    public $model;

    public $plugins = ['wholerow', 'contextmenu', 'dnd', 'types', 'state'];

    public $routes = [
        'getTree' => null,
        'move' => null,
        'open' => null,
        'edit' => null,
        'create' => null,
        'delete' => null,
        'clone' => null,
    ];

    public $types = [
        'show' => [
            'icon' => 'fa fa-file-o',
        ],
        'list' => [
            'icon' => 'fa fa-list',
        ],
    ];

    public function run()
    {
        $id = $this->getId();

        $options = [
            'plugins' => $this->plugins,
            'core' => [
                'check_callback' => true,
                'data' => [
                    'url' => new JsExpression(
                        "function (node) {
                            return " . Json::encode(Url::to($this->routes['getTree'])) . ";
                        }"
                    ),
                    'data' => new JsExpression(
                        "function (node) {
                        return { 'id' : node.id };
                        }"
                    ),
                ],
            ],
            'contextmenu' => [
                'select_node' => false,
                'items' => [
                    'edit' => [
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
                    ],
                    'open' => [
                        'label' => Yii::T('app', 'Open'),
                        'icon' => 'fa fa-folder-open',
                        'action' => new JsExpression(
                            "function (a) {
                                var \$object = $(a.reference[0]);
                                document.location = " . Json::encode(Url::to($this->routes['open'])) . "
                                + '?parent_id=' + \$object.attr('data-id');
                            }"
                        ),
                    ],
                    'create' => [
                        'label' => Yii::t('app', 'Create'),
                        'icon' => 'fa fa-plus-circle',
                        'action' => new JsExpression(
                            "function (a) {
                                var \$object = $(a.reference[0]);
                                document.location = " . Json::encode(Url::to($this->routes['create'])) . "
                                + '?parent_id=' + \$object.attr('data-id');
                            }"
                        ),
                    ],
                    'delete' => [
                        'label' => Yii::T('app', 'Delete'),
                        'icon' => 'fa fa-trash-o',
                        'action' => new JsExpression(
                            "function (a) {
                                var \$object = $(a.reference[0]);
                                document.location = " . Json::encode(Url::to($this->routes['delete'])) . "
                                + '?id=' + \$object.attr('data-id');
                            }"
                        ),
                    ],
                ],
            ],
            'state' => [
                'key' => 'jstree' . Yii::$app->db->schema->getRawTableName($this->model->tableName()),
                'filter' => new JsExpression(
                    "function (a) {
                        if ('' === window.location.search) {
                            var root = jstree.jstree().get_node('#').children;
                            a.core.selected = [root.shift()];
                        }
                        return a;
                    }"
                ),
            ],
        ];
        if (count($this->types) > 0) {
            $options['types'] = $this->types;
        }



        return $this->render(
            'JSTree',
            [
                'model' => $this->model,
                'routes' => $this->routes,
                'id' => $id,
                'options' => Json::encode($options),
            ]
        );
    }
}
