<?php

namespace app\backend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

class JSTreeTrash extends Widget
{

    public $model;

    public $plugins = ['wholerow', 'contextmenu', 'dnd', 'types', 'state'];

    public $routes = [
        'getTree' => null,
        'restore' => null,
        'delete' => null,
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
                    'restore' => [
                        'label' => Yii::T('app', 'Restore'),
                        'icon' => 'fa fa-refresh',
                        'action' => new JsExpression(
                            "function (a) {
                                var \$object = $(a.reference[0]);
                                document.location = " . Json::encode(Url::to($this->routes['restore'])) . "
                                + '?id=' + \$object.attr('data-id');
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
        ];
        if (count($this->types) > 0) {
            $options['types'] = $this->types;
        }

        return $this->render(
            'JSTreeTrash',
            [
                'model' => $this->model,
                'routes' => $this->routes,
                'id' => $id,
                'options' => Json::encode($options),
            ]
        );
    }
}
