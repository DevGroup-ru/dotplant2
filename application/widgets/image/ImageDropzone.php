<?php

namespace app\widgets\image;

use app\models\Image;
use devgroup\dropzone\DropZone;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class ImageDropzone extends DropZone
{
    public $removeUrl = 'remove';
    public $url = 'upload';
    public $objectId;
    public $modelId;

    public $uploadDir = 'upload';

    public static function saveThumbnail($dir, $filename, $width=80, $height=80)
    {
        if (trim($filename) && file_exists(Yii::getAlias($dir . '/' . $filename))) {

            $image = \yii\imagine\Image::thumbnail(Yii::getAlias($dir . '/' . $filename), $width, $height, ManipulatorInterface::THUMBNAIL_INSET);
            $image->save($dir . '/small-' . $filename);


            return 'small-' . $filename;
        }

        return '';
    }

    protected function addFiles($files = [])
    {
        $path = Yii::getAlias('@webroot' . $this->uploadDir . '/');

        $i = 0;
        foreach ($files as $file) {
            $fhName = 'file_' . $i++;

            // Create the mock file:
            $this->getView()->registerJs(
                'var '
                . $fhName
                . ' = { name: "'
                . $file['name']
                . '", size: '
                . (file_exists($path . $file['name']) ? filesize($path . $file['name']) : 0)
                . ' };'
            );
            // Call the default addedfile event handler
            $this->getView()->registerJs(
                $this->dropzoneName . '.emit("addedfile", ' . $fhName . ');'
            );
            // And optionally show the thumbnail of the file:
            $this->getView()->registerJs(
                $this->dropzoneName . '.emit("thumbnail", ' . $fhName . ', "' . $file['thumbnail'] . '");'
            );
            $this->getView()->registerJs(
                'jQuery(' . $fhName . '.previewElement).find("[name=\"id[]\"]").val(' . $file['id'] . ');
                jQuery(' . $fhName . '.previewElement).data("filename", "' . $file['name'] . '");
                jQuery(' . $fhName . '.previewElement).find(".description textarea").text("'
                . $file['description'] . '");
                jQuery(' . $fhName . '.previewElement).find(".description textarea").attr("name", "description['
                . $file['id'] . ']")'
            );
        }
    }

    protected function createDropzone()
    {
        $this->getView()->registerJs(
            'var ' . $this->dropzoneName . ' = new Dropzone("#' . $this->id . '", ' . Json::encode($this->options) . ');'
        );
    }

    public function run()
    {
        parent::run();

        ImageDropzoneAsset::register($this->getView());
    }

    public function init()
    {
        parent::init();

        Html::addCssClass($this->htmlOptions, 'custom-dz');

        /** @var Image[] $files */
        $files = Image::find()->where(
            [
                'and',
                'object_id = :objectId',
                'object_model_id = :modelId',
            ],
            [
                ':objectId' => $this->objectId,
                ':modelId' => $this->modelId,
            ]
        )->orderBy(['sort_order' => SORT_ASC])->all();

        $path = Yii::getAlias('@webroot' . $this->uploadDir);

        /** Image $file */
        foreach ($files as $file) {
            if (!isset($file->thumbnail_src) || !trim($file->thumbnail_src)) {
                $file->thumbnail_src = Yii::getAlias(
                    '@web' . $this->uploadDir . '/' . self::saveThumbnail($path, $file->filename)
                );
                $file->save(false, ['thumbnail_src']);
            }
            $this->storedFiles[] = [
                'id' => $file->id,
                'name' => $file->filename,
                'thumbnail' => Yii::$app->request->baseUrl.$file->thumbnail_src,
                'description' => $file->image_description,
            ];
        }

        $params = ArrayHelper::merge(
            isset($this->options['params']) ? $this->options['params'] : [],
            [
                'objectId' => $this->objectId,
                'modelId' => $this->modelId,
            ]
        );
        $this->sortable = true;
        $this->options = ArrayHelper::merge(
            $this->options,
            [
                'acceptedFiles' => 'image/*',
                'params' => $params,
                'previewTemplate' =>
                    '<div class="file-row">
                        ' . Html::input('hidden', 'id[]') . '
                        <!-- This is used as the file preview template -->
                        <div>
                            <span class="preview"><img style="width: 80px; height: 80px;" data-dz-thumbnail /></span>
                        </div>
                        <div>
                            <p class="name" data-dz-name></p>
                            <div class="dz-error-message"><span data-dz-errormessage></span></div>
                        </div>
                        <div class="description">
                            ' . Html::textarea('description', '', ['style' => 'width: 100%; min-width: 80px; height: 80px;']) . '
                        </div>
                        <div>
                            <p class="size" data-dz-size></p>
                            <div class="dz-progress progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                              <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                            <div class="dz-success-mark"><span>✔</span> OK</div>
                            <div class="dz-error-mark"><span>✘</span> ERROR</div>
                        </div>
                        <div>
                          <button data-dz-remove class="btn btn-danger delete">
                            <i class="glyphicon glyphicon-trash"></i>
                            <span>Delete</span>
                          </button>
                        </div>
                      </div>',
                'thumbnailWidth' => '80',
                'thumbnailHeight' => '80',
                'previewsContainer' => "#{$this->id}",
            ]
        );

        $this->eventHandlers = [
            'removedfile' => 'function(file) {
                jQuery.get(
                    "' . Url::toRoute($this->removeUrl) . '",
                    {
                        "id" : jQuery(file.previewElement).find("[name=\"id[]\"]").val(),
                        "filename" : jQuery(file.previewElement).data("filename")
                    }
                ).done(function (data) { return data });
            }',
            'success' => 'function(file, response) {
                response = jQuery.parseJSON(response);
                jQuery(file.previewElement).find("[data-dz-name]").text(response.filename);
                jQuery(file.previewElement).data("filename", response.filename);
                jQuery(file.previewElement).find("[name=\"id[]\"]").val(response.afterUpload.id);
                jQuery(file.previewElement).find(".description textarea").attr("name", "description["+response.afterUpload.id+"]");
            }',
            'complete' => 'function(file) {
                jQuery(file.previewElement).removeClass("dz-processing");
            }',
        ];
    }
}
