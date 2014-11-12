<?php

namespace app\widgets\dropzone;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class DropZone extends Widget
{
    public $model;
    public $attribute;
    public $htmlOptions = [
        'class' => 'dropzone',
    ];
    public $name;
    public $options = [];
    public $eventHandlers = [];
    public $url;
    public $storedFiles = [];
    public $sortable = false;
    public $sortableOptions = [];

    protected $dropzoneName = 'dropzone';

    public function init()
    {
        parent::init();

        $this->dropzoneName = 'dropzone_' . $this->id;
    }

    private function registerAssets()
    {
        DropZoneAsset::register($this->getView());
        $this->getView()->registerJs('Dropzone.autoDiscover = false;');
    }

    protected function addFiles($files = [])
    {
        foreach ($files as $file) {
            // Create the mock file:
            $this->getView()->registerJs(
                'var mockFile = { name: "' . $file['name'] . '", size: ' . $file['size'] . ' };'
            );
            // Call the default addedfile event handler
            $this->getView()->registerJs(
                $this->dropzoneName . '.emit("addedfile", mockFile);'
            );
            // And optionally show the thumbnail of the file:
            $this->getView()->registerJs(
                $this->dropzoneName . '.emit("thumbnail", mockFile, "' . $file['thumbnail'] . '");'
            );
        }
    }

    protected function decrementMaxFiles($num)
    {
        $this->getView()->registerJs(
            'if (' . $this->dropzoneName . '.options.maxFiles > 0) { '
            . $this->dropzoneName . '.options.maxFiles = '
            . $this->dropzoneName . '.options.maxFiles - ' . $num . ';'
            . ' }'
        );
    }

    protected function createDropzone()
    {
        $options = Json::encode($this->options);
        $this->getView()->registerJs($this->dropzoneName . ' = new Dropzone("#' . $this->id . '", ' . $options . ');');
    }

    public function run()
    {
        if (empty($this->name) && (!empty($this->model) && !empty($this->attribute))) {
            $this->name = Html::getInputName($this->model, $this->attribute);
        }

        if (empty($this->url)) {
            $this->url = Url::toRoute(['site/upload']);
        }

        $options = [
            'url' => $this->url,
            'paramName' => $this->name,
            'params' => [],
        ];

        if (Yii::$app->request->enableCsrfValidation) {
            $options['params'][Yii::$app->request->csrfParam] = Yii::$app->request->getCsrfToken();
        }

        $this->htmlOptions['id'] = $this->id;
        $this->options = ArrayHelper::merge($this->options, $options);
        echo Html::tag('div', '', $this->htmlOptions);

        $this->registerAssets();

        $this->createDropzone();

        foreach ($this->eventHandlers as $event => $handler) {
            $handler = new \yii\web\JsExpression($handler);
            $this->getView()->registerJs(
                $this->dropzoneName . ".on('{$event}', {$handler})"
            );
        }

        $this->addFiles($this->storedFiles);
        $this->decrementMaxFiles(count($this->storedFiles));

        if ($this->sortable) {
            $options = Json::encode($this->sortableOptions);
            $this->getView()->registerJs("jQuery('#{$this->id}').sortable(" . $options . ");");
        }
    }
}
